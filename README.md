This extension looks up UK regional/political data from MapIt (https://mapit.mysociety.org/) and assigns it to custom fields. 
This allows you to search for everyone in particular regions, or Parliamentary Constituencies, and set up smart groups etc.

It looks up data in real time whenever a primary address is added or edited. Currently it looks up:

* Parliamentary Constituency
* Ward
* Highest Local Authority
* Second highest Local Authority
* Regional Authority (there are 12)
* Welsh Assembly Constituency
* Welsh Assembly Region
* Scottish Parliamentary Constituency
* Scottish Parliamentary Region
* UK Country

## Caveats

* requires full postcodes
* only works for postcodes (though there's some limited code in there for handling lat/long lookups in some situations specific to the BHA that you can customise)
* doesn't run when importing data, as MapIt is rate-limited to 1 per second and it'd take forever
* won't look up your existing data

and it's still fairly hacky, particularly in that the custom field values are hard-coded (sorry). But works!

## To use

1. Install the extension.
2. If you're a charity, sign up for a MapIt API key. As of writing it's free for 10,000 calls per month. You can use it for free without, though you get far fewer lookups. The extension will still work without an API key.
3. Enter the API key at [normal Civi URL]/civicrm/mapit/settings
4. Create Custom Fields for your desired data, and note their ID numbers
5. Edit politicaldata.php and near the bottom you'll see a $customParams array where the data is assigned to whichever custom field. Edit the numbers in custom_xxx appropriately.
6. Try editing a test record, then refresh the record. If you have an API key you can check on mapit to see whether the request was generated at all.

## Improvements

We'd love to see someone run with this! It really needs:

* Add an interface to assign custom fields
* Really needs a scheduled job to loop through imports / existing data look them up slowly

## Looking up data for existing records

We filled in our existing records using a giant spreadsheet of doom from: https://geoportal.statistics.gov.uk/, then used MapIt for ongoing work.
