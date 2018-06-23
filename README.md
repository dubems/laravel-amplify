# laravel-amplify
Laravel Library for integrating Amplify pay 

## Installation

[PHP](https://php.net) 5.4+ and [Composer](https://getcomposer.org) are required.

To get the latest version of Laravel Amplify, require like below
```
" composer require dubems/laravel-amplify"
```

You'll then need to run `composer install` or `composer update` to download it and have the autoloader updated.

Once Laravel Amplify is installed, you need to register the service provider. Open up `config/app.php` and add the following to the `providers` key.

* `Dubems\Amplify\AmplifyServiceProvider::class`

Also, register the Facade like so:

```php
'aliases' => [
    ...
    'Amplify' => Dubems\Ampify\Facades\Amplify::class,
    ...
]
```

## Configuration

You can publish the configuration file using this command:

```bash
php artisan vendor:publish --provider="Dubems\Amplify\AmplifyServiceProvider"
```

A configuration-file named `amplify.php` with some defaults will be placed in your `config` directory:

```php
<?php

return [
   /**
    * Merchant ID gotten from your Amplify dashboard
    */

  'merchantId'=> getenv('AMPLIFY_MERCHANT_ID'),

    /**
     *  API Key from amplify dashboard
     */
    'apiKey' => getenv('AMPLIFY_API_KEY'),

    /**
     * Amplify payment Url
     */
    'paymentUrl' => getenv('AMPLIFY_PAYMENT_URL'),

    /**
     * Redirect Url after successful transaction
     */
    'redirectUrl' => getenv('AMPLIFY_REDIRECT_URL')

];
```


##General payment flow

Though there are multiple ways to pay an order, most payment gateways expect you to follow the following flow in your checkout process:

###1. The customer is redirected to the payment provider
After the customer has gone through the checkout process and is ready to pay, the customer must be redirected to site of the payment provider.

The redirection is accomplished by submitting a form with some hidden fields. The form must post to the site of the payment provider. The hidden fields minimally specify the amount that must be paid and some other fields

###2. The customer pays on the site of the payment provider
The customer arrives on the site of the payment provider and gets to choose a payment method. All steps necessary to pay the order are taken care of by the payment provider.

###3. The customer gets redirected back
After having paid the order the customer is redirected back. In the redirection request to the shop-site some values are returned.

## Usage

Open your .env file and add your public key, secret key, merchant email and payment url like so:

```php
AMPLIFY_MERCHANT_ID=XXXXXXX
AMPLIFY_API_KEY=XXXXXX
AMPLIFY_PAYMENT_URL=https://api.amplifypay.com
AMPLIFY_REDIRECT_URL=https://xxxxx
```

Set up routes and controller methods like so:

```php
Route::post('/pay', 'PaymentController@redirectToGateway')->name('pay'); // Laravel 5.1.17 and above

Route::get('/payment/callback', 'PaymentController@handleGatewayCallback');
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Paystack;

class PaymentController extends Controller
{

    /**
     * Redirect the User to Amplify Payment Page
     * @return Url
     */
    public function redirectToGateway()
    {
           return Amplify::getAuthorizationUrl()->redirect();
    }

    /**
     * Get Amplify payment information
     * @return void
     */
     public function handleGatewayCallback()
      {
           $response = Amplify::handlePaymentCallback();
           dd($response);

        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
     }
}
```

Other methods and example usage can be found below
```php
    /** Create Subscription */
  public function createSubscription()
    {
        $data = ['planName' => 'Sliver members', 'frequency' => 'Weekly'];
        $response = Amplify::createSubscription($data);
        dd($response);
    }

    /** Delete a particular subscription */  */
    public function deleteSubscription()
    {
        $id = 'xyz';
        $response = Amplify::deleteSubscription($id);
        dd($response);
    }

    /** Update a particular subscription */
 public function updateSubscription()
    {
        $data = ['planName' => 'Gold members', 'frequency' => 'Weekly'];
        $planId = 'xyz';
        $response = Amplify::updateSubscription($planId, $data);
        dd($response);
    }

    /** Get a particular subscription */
public function fetchSubscription()
   {
       $id = 'id';
       $response = Amplify::fetchSubscription($id);
       dd($response);
   }

    /** Fetch all subscription */
public function fetchAllSubscription()
    {
        $allSub = Amplify::fetchAllSubscription();
        dd($allSub);
    }




```

A sample form will look like so:

```html
<form method="POST" action="{{ route('pay') }}" accept-charset="UTF-8" class="form-horizontal" role="form">
        <div class="row" style="margin-bottom:40px;">
          <div class="col-md-8 col-md-offset-2">
            <p>
                <div>
                   A cup of coffee
                    ₦ 800
                </div>
            </p>
            <input type="hidden" name="email" value="nriagudubem@gmail.com"> {{-- required --}}
            <input type="hidden" name="description" value="XYZ">
            <input type="hidden" name="amount" value="800"> {{-- required in naira --}}
            <input type="hidden" name="name" value="Nriagu Dubem">
            <input type="hidden" name="planId" value="Your plan ID">
            {{ csrf_field() }} {{-- works only when using laravel 5.1, 5.2 --}}

             <input type="hidden" name="_token" value="{{ csrf_token() }}"> {{-- employ this in place of csrf_field only in laravel 5.0 --}}


            <p>
              <button class="btn btn-success btn-lg btn-block" type="submit" value="Pay Now!">
              <i class="fa fa-plus-circle fa-lg"></i> Pay Now!
              </button>
            </p>
          </div>
        </div>
</form>
```

```bash
PAN = 5060 9905 8000 0217 499
EXPYEAR = 20
EXPMONTH = 04
CVV = 111

If prompted for Amount Validation, Enter 1.10
```

## Todo

* Add Comprehensive Tests

## Contributing

Please feel free to fork this package and contribute by submitting a pull request to enhance the functionalities.

## How can I thank you?

Star the github repo, I'd love the attention! You can also share the link for this repository on Twitter or HackerNews? Spread the word!

Don't forget to [follow me on twitter](https://twitter.com/nriagudubem)!

Thanks!
Nriagu Chidubem.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.