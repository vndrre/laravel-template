## Laravel basics 2

Create an e-mail notification app.

## Prerequesites
- [Server requirements](https://laravel.com/docs/10.x/deployment#server-requirements)
- [Mailtrap account](https://mailtrap.io/)

## Step 1 - Setup

- Create an `.env` file based on `.env_example`
- Open mailtrap and select _laravel 9+_ from the integrations dropdown.
 <img width="1298" alt="280022168-9f5ea50c-e765-4079-904d-0b64c85290c1" src="https://github.com/kuressaareametikool/laravel-template/assets/56673494/d9ea6128-48d2-465e-9db9-e98dcb55b88e">

- Replace `MAIL_*` variables in the `.env` file with the ones provided by mailtrap.

## Step 2 - artisan commands

- Create an artisan command named `TimetableNotification`
- Create a mailable named `Timetable`

### Resources
- [Generating Mailables](https://laravel.com/docs/10.x/mail#generating-mailables)
- [Writing Commands](https://laravel.com/docs/10.x/artisan#writing-commands)

## Step 3 - Http client

_An example timetable request_

`https://tahvel.edu.ee/hois_back/timetableevents/timetableByGroup/38?from=2023-10-30T00:00:00Z&studentGroups=5901&thru=2023-11-05T00:00:00Z`

- Inside the `TimetableNotification` command's `handle()` method fetch data from the API endpoint using Http client.
- Using `dd()` helper method dump out the response.
- To run the command in terminal: `php artisan $signature` _(replace `$signature` with the respective variables string value from the `TimetableNotification` class)_
- Response in the terminal should look like this:
- <img width="714" alt="280034303-5dcc90b3-6b6c-4609-9418-9f3633e23a7c" src="https://github.com/kuressaareametikool/laravel-template/assets/56673494/4e4853ff-27ab-41b7-be57-0e5f188214b7">

- To get the actual data from the response we need to use the `json()` method _(Check the "making request" docs)_
- Running the command with the added `json()` method we should see the following output:
- <img width="714" alt="280035882-c5455ce5-246a-4351-a01b-3a684c7f9fae" src="https://github.com/kuressaareametikool/laravel-template/assets/56673494/c35e6b92-7f1a-447f-ad1f-3f98cc8a2843">


### Resources
- [Http client: making requests](https://laravel.com/docs/10.x/http-client#making-requests)
- [dd() helper](https://laravel.com/docs/10.x/helpers#method-dd)

## Step 4 - URL & query params

_In order to make the request dynamic we need to split up the API endpoint._

The API endpoint has two parts:
- URL: `https://tahvel.edu.ee/hois_back/timetableevents/timetableByGroup/38`
- Query: `?from=2023-10-30T00:00:00Z&studentGroups=5901&thru=2023-11-05T00:00:00Z`
Each of the query params has a key & value pair.
- Following the [query params](https://laravel.com/docs/10.x/http-client#get-request-query-parameters) example from the docs, update the Http request.
- Run the command to validate, result should be the same as before.

### Resources
- [Get(): query params](https://laravel.com/docs/10.x/http-client#get-request-query-parameters)
 

## Step 5 - Dynamic query

_**!!** For testing purposes we are using current week instead of next week as next week has no data._


Using [Carbon](https://carbon.nesbot.com/docs/) and [now()](https://laravel.com/docs/10.x/helpers#method-now) replace the the `from` & `thru` hardcoded dates.
- to get the start of week we can use `now()->startOfWeek()` & for the end of week we can use `now()->endOfWeek()`
- to get next week simply change the code as follows: `now()->addWeek(1)->startOfWeek`.
- Create two variables, `$startDate` & `$endDate` that equal next weeks start day and end day respectivly.
- Replace the string dates in the query with the variables.
- 
**!!** We also need to format the date variables inside the query.
- `2023-11-05T00:00:00Z` is an ISO standard date, so adding `toIsoString()` to the end of both `from` & `thru` values should suffice.
- Once again run the command to validate.

### Step 6 - Data transformations
- Extract the `timetableEvents` from the response data and save them in a `collection()`
- Sort the events by `date` & `timeStart`
- Group the events by localized day name e.g "kolmapäev".

<details>
<summary>Solution (Dont peek unless you have tried everything else.)</summary>
 
```PHP
$timetableEvents = collect($data['timetableEvents'])
        ->sortBy(['date', 'timeStart'])
        ->groupBy(function ($event) {
            return Carbon::parse($event['date'])->locale('et_EE')->dayName;
        });
```
</details>

### Resources
- [Collections](https://laravel.com/docs/10.x/collections)
 
## Step 7 - Configuring the mailable

- Open the mailable `Timetable` and add 3 protected variables to the constructor.
  - `$timetableEvents` - typehint should be a Collection
  - `$startDate` - typehint should be Carbon
  - `$endDate` -- typehint should be Carbon
- Create a new folder `emails` in `resources/views`
- Add a new blade file in the `emails` folder named `timetable.blade.php`
- In the `Timetable` mailable, change the named parameter `view:` in the content method to `markdown:` & change the value to `emails.timetable`.
- add a new named parameter `with:` & for the value we need to add the variables from the consturctor as key => value pairs.

_Check the docs below for guidance._
  ### Resources
  - [Mail data via the with method](https://laravel.com/docs/10.x/mail#via-the-with-parameter)

## Step 8 - Mail template
To configure the mail template we should expose the mailable as a route.
- Add a new route to `web.php`
- The route should return an instance of the mailable `Timetable` and it should include the 3 variables from step 7
  
  ```PHP
  Route::get('/mailable', function () {
    
    // All the code logic from timetableNotification commands handle() method
    
    return new Timetable($timetableEvents, $startDate, $endDate);
    });
    ```
- now by navigation to `/mailable` in the browser you should be able to see an error
  - ```DOMDocument::loadHTML(): Argument #1 ($source) must not be empty ```
- Using markdown & blade syntax configure the ´timetable.blade.php´ to your liking.
  - On the template you have access to the variables passed on from the `content` methods `with:` parameter.

  ### Resources
  - [Writing markdown messages](https://laravel.com/docs/10.x/mail#writing-markdown-messages)

  ## Step 9 - Sending the email
  - Open up the `TimetableNotification` command and remove all/any `dd()` methods and end the command by sending out an email using `Timetable` mailable
  -  Run the command once more.
  - Check mailtrap inbox to see if you have recieved any e-mails.
 
- <img width="1531" alt="280128001-5c6d7fd7-6d6b-48c5-baf2-934643961bba" src="https://github.com/kuressaareametikool/laravel-template/assets/56673494/63240631-d20e-4500-8636-56c98d54d1d7">

 
<details>
<summary>Solution (Dont peek unless you have tried everything else.)</summary>

```PHP
   Mail::to('test@test.ee')->send(new Timetable($timetableEvents, $startDate, $endDate));
```
</details>

    

  
