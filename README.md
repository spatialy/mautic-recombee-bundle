# MauticRecombeeBundle

Increase your customer satisfaction and spending with Amazon and Netflix-like AI powered recommendations. Applicable to your home page, product detail, emailing campaigns and much more. Quick and Easy Integration into Your Environment.

## Create Recombee account for free

1. Go to  [www.recombee.com](https://www.recombee.com)  and create account.  
2. Free plan up to 100 000 recommendation requests is good choice.  
3. Then go to Accounts > Organizations > your organization > edit database and copy API credits

![](https://docs.mtcextendee.com/assets/images/image03.jpg?v13024233387251)

## Import data (items, users)

Recombee combine data about items and user and related data between both.  
Then before start working with Recombee we need import items (required) and contacts (optional).  
Items import

### Import items

Items are parsed from your external JSON file. This command should run initial, but also you can update your items one time per 24/48 hours.

`php app/console mautic:recombee:import ---type=items`  
`--file="path/to/items.json"`

Results from command

![](https://docs.mtcextendee.com/assets/images/image02.jpg?v13024233387251)

### Import contacts

Contacts are imported from Mautic contacts.  
If you are working on new Mautic installation, then you can skip this step. Contacts import is initial and you should run it first time. Then Mautic will send data about contacts realtime.  
  
Command:

`php app/console mautic:recombee:import --type=contacts`

Results from command

![](https://docs.mtcextendee.com/assets/images/image01.jpg?v13024233387251)

## Send data realtime by API

You can send based interactions between items/user by API.  
You have to setup  [Mautic API](https://github.com/mautic/api-library).  
Based init code looks like:

`$api = new MauticApi();`  
`$apiRequest = $api->newApi('api', $auth, $apiUrl);`

Interactions

AddCartAddition

Adds a cart addition of a given item made by a given user.

`$component = 'AddCartAddition';`  
`$options = ['userId' => 1, 'itemdId' => 1, 'amount'=>1, 'price'=>99];`  
`$apiRequest->makeRequest('recombee/'.$component, $options, 'POST');`

DeleteCartAddition

Adds a cart addition of a given item made by a given user.

`$component = 'DeleteCartAddition';`  
`$options = ['userId' => 1, 'itemdId' => 1];`  
`$apiRequest->makeRequest('recombee/'.$component, $options, 'POST');`

AddPurchase

Adds a purchase of a given item made by a given user.

`$component = 'AddPurchase';`  
`$options = ['userId' => 1, 'itemdId' => 1,`  
`'amount' => 1, 'price' => 99, 'profit' => 9];`  
`$apiRequest->makeRequest('recombee/'.$component, $options, 'POST');`

DeletePurchase

Deletes an existing purchase

`$component = 'DeletePurchase';`  
`$options = ['userId' => 1, 'itemdId' => 1];`  
`$apiRequest->makeRequest('recombee/'.$component, $options, 'POST');`

AddDetailView

Adds a detail view of a given item made by a given user.

`$component = 'AddDetailView';`  
`$options = ['userId' => 1, 'itemdId' => 1];`  
`$apiRequest->makeRequest('recombee/'.$component, $options, 'POST');`

## Send data realtime by Mautic pixel

Add Mautic tracking code to website

First you have to add Mautic tracking code  [to your website](https://www.mautic.org/docs/en/contacts/contact_monitoring.html#javascript-js-tracking)

Then edit your tracking pixel on each product page with Recombee code to pageview event. Data send by pixel improve personalization products for your contacts. Example how to add custom parametrs to Mautic pageview event:

AddDetailView

Adds a detail view of a given item made by a given user.

`mt('send', 'pageview', { Recombee: '{"AddDetailView":{"itemId":1}}' });`
