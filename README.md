# Laravel Package that allows central monitoring of all Rackbeat integrations

## 1. `composer require kg-bot/rackbeat-integration-dashboard` inside your integration project

## 2. Export configurations `php artisan vendor:publish` for this package and change your Connection class inside

## 3. Run migrations

```bash
$ php artisan migrate
```

## 4. Run `php artisan make:dashboard-token`

## 5. Add `rackbeat-integration-dashboard/*` to your `VerifyCsrfToken` middleware `$except` property

This package requires you to use Laravel Jobs for all of your transfers and tasks between Rackbeat and 3rd party integrations.

Each of your Job classes must extend `KgBot\RackbeatDashboard\Classes\DashboardJob` and it must have special `__construct` code.

Jobs are not dispatched directly, instead you need to create a new `KgBot\RackbeatDashboard\Models\Job` model and it will automatically dispatch job using observer.

If you need any special constructor data you must send them in `Job` model's `create()` method, they will be serialized and saved in database so you will have to use them as so from you Job's constructor.

Example of this would look like this:

```
// App\Jobs\Webhooks\TransferInvoice.php

<?php

namespace App\Jobs\Webhooks;

use App\Connection;
use App\Transformers\CustomerTransformer;
use App\Transformers\InvoiceTransformer;
use App\Transformers\SupplierTransformer;
use App\Transformers\TransformerInterface;
use Illuminate\Support\Facades\Log;
use KgBot\Billy\Billy;
use KgBot\Billy\Models\Customer;
use KgBot\Billy\Models\Supplier;
use KgBot\RackbeatDashboard\Classes\DashboardJob;
use KgBot\RackbeatDashboard\Models\Job;
use Rackbeat\Utils\Model;

class TransferInvoice extends DashboardJob
{

	/**
	 * @var $conn Connection
	 */
	protected $conn;

	/**
	 * @var $type string
	 */
	protected $type;

	/**
	 * @var $request array
	 */
	protected $request;

	protected $tries = 1;

	protected $timeout = 0;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct( Job $job, $connection, $request, string $type = 'customer' ) {

		parent::__construct( $job );
		$connection = Connection::find( $connection->id );

		$this->conn    = $connection;
		$this->type    = $type;
		$this->request = (array) $request;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function execute() {
		$this->conn->focus();

		// todo Determine is this supplier or customer invoice so we know what integration to check if enabled

		try {

			if ( $this->type === 'customer' ) {
				$invoice = $this->conn->createRackbeatClient()->rb->customer_invoices()->find( $this->request['key'] );

				if ( ! $this->conn->hasDebtorIntegration() ) {
					return;

				}
			} else {

				$invoice = $this->conn->createRackbeatClient()->rb->supplier_invoices()->find( $this->request['key'] );

				if ( ! $this->conn->hasCreditorIntegration() ) {

					return;

				}
			}

			$this->jobModel->updateProgress( 10 );
		} catch ( \Exception $exception ) {

			$this->jobModel->updateProgress( 5 );
			Log::error( 'Can\'t get invoice from webhook: ' . $exception->getMessage() );
			throw $exception;
		}


		$this->createInvoice( $this->conn, $invoice );
		$this->jobModel->updateProgress( 100 );
	}

	/**
	 * @param Connection $connection
	 * @param Model      $invoice
	 *
	 * @return bool
	 */
	protected function createInvoice( Connection $connection, Model $invoice ) {

		$billy    = $connection->createBillyClient();
		$rackbeat = $connection->createRackbeatClient();

		$type      = ( $invoice->getEntity() === 'supplier-invoices' ) ? 'supplier' : 'customer';
		$rb_entity = $type . 's';

		$contact = $this->findOrCreateContact( $rackbeat->rb->{$rb_entity}()->find( $invoice->{$type . '_id'} ), $billy, $type );

		$this->jobModel->updateProgress( 50 );
		$invoice = InvoiceTransformer::rackbeat( $connection, $invoice );

		$invoice['contactId'] = $contact->id;

		if ( $type === 'customer' ) {
			$this->jobModel->updateProgress( 80 );
			$this->createBillyInvoice( $invoice, $billy );
		} else {
			$this->jobModel->updateProgress( 80 );
			$this->createBillyBill( $invoice, $billy );
		}

		return true;
	}

	/**
	 * @param        $contact Model
	 * @param        $billy   Billy
	 * @param string $type    string
	 *
	 * @return Supplier|Customer
	 */
	protected function findOrCreateContact( $contact, $billy, $type = 'supplier' ) {
		switch ( $type ) {

			case 'customer':
				$billy = $billy->customers();
				/**
				 * @var $transformer TransformerInterface
				 */
				$transformer = new CustomerTransformer( $this->conn );
				break;
			default:
				$billy       = $billy->suppliers();
				$transformer = new SupplierTransformer( $this->conn );
		}

		$billy_contact = $billy->get( [
			[ 'contactNo', '=', $contact->number ]
		] )->first();

		if ( ! $billy_contact ) {

			$billy_contact = $billy->create( $transformer->fromRackbeat( $contact ) );
		}

		return $billy_contact;
	}

	/**
	 * @param $invoice
	 * @param $billy Billy
	 */
	protected function createBillyInvoice( $invoice, $billy ) {

		$billy->invoices()->create( $invoice );
	}

	/**
	 * @param $invoice
	 * @param $billy Billy
	 */
	protected function createBillyBill( $invoice, $billy ) {

		$billy->bills()->create( $invoice );
	}
}

```

And this is how you would start this job from your controller

```
// App\Http\Controllers\Webhooks\InvoiceController.php

<?php

namespace App\Http\Controllers\Webhooks;

use App\Connection;
use App\Jobs\Webhooks\TransferInvoice;
use KgBot\RackbeatDashboard\Models\Job;

class InvoiceController extends Controller
{
	public function test() {

		Job::create( [

			'command'    => TransferInvoice::class,
			'queue'      => 'rackbeat-dashboard',
			'args'       => [ Connection::find( 1 ), [ 'key' => 3001 ], 'customer' ],
			'title'      => 'Transfer Invoice',
			'created_by' => 1,
		] );

		return redirect()->to( '/' );
	}
}
```

`created_by` parameter is Connection ID for which you create this model/job.

If you need any more info feel free to contact maintainers, read this code or read Billy integration code because this is all used inside that integration.