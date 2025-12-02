# Flash Sale API (Laravel)

This is a small Laravel 12 API built for a flash-sale use case.\
The API handles temporary stock holds, order creation, and an idempotent
payment webhook.\
The main focus was making the flow safe under concurrency and avoiding
any overselling.

MySQL (InnoDB) is used for row-level locking, and the project includes a
background job for expiring holds.

------------------------------------------------------------------------

## Running the project

1.  Clone the repository\

2.  Install dependencies:
- composer install

3.  Create the `.env` file:
- cp .env.example .env

4.  Update your database credentials in `.env`

5.  Generate the application key:
- php artisan key:generate

6.  Run migrations and seeders:
- php artisan migrate --seed

7.  Start the server:
- php artisan serve

If you want automatic expiry of holds to run continuously, start the
scheduler:

- php artisan schedule:work

------------------------------------------------------------------------

## API Endpoints

### GET /api/products/{id}

Returns product information along with the currently available stock.

### POST /api/holds

Creates a temporary hold (around 2 minutes).

Example body:

``` json
{
  "product_id": 1,
  "qty": 2
}
```

### POST /api/orders

Creates an order from a valid, unexpired hold.

Example body:

``` json
{
  "hold_id": 10
}
```

### POST /api/payments/webhook

Processes a payment result.\
This endpoint is idempotent using the `idempotency_key`.

Example body:

``` json
{
  "order_id": 10,
  "status": "success",
  "idempotency_key": "unique-key"
}
```

------------------------------------------------------------------------

## Implementation notes

-   Holds reduce available stock immediately\
-   Hold creation and stock checks use transactions with row-level
    locking\
-   A hold can only be used once\
-   Expired holds are not accepted when creating orders\
-   Payment webhook updates the order status and releases the hold on
    failures\
-   Webhook retries are handled safely through the idempotency key\
-   A scheduled job cleans expired holds\
-   Product endpoint uses caching for faster reads (availability is
    always computed fresh)

------------------------------------------------------------------------

## Tests

The test suite includes:

-   Parallel hold attempts to ensure no overselling\
-   Hold expiry behaviour\
-   Idempotent webhook handling\
-   Webhook arriving before the client receives the order response

Run tests using:

    php artisan test

------------------------------------------------------------------------

This implementation was kept simple and focused on correctness under
concurrency.
