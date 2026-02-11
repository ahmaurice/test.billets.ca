## Installation

### Using Docker

```bash
# 1. Clone the repository
git clone <repository-url>
cd Challenge

# 2. Build and start the container
docker-compose up -d --build

# 3. Install dependencies
docker exec -it subscription_challenge_app bash
composer install

# 4. Verify installation
php bin/console list

# 5. Entity migrations
php bin/console doctrine:migrations:migrate

```

## Usage

#### 1. Create a User

```bash
php bin/console app:create-user "Maurice" "maurice@elian.com"
```

#### 2. Create a Product

```bash
php bin/console app:create-product \
  "Premium SaaS" \
  "Premium software subscription" \
  --pricing="Annual:299.99:CAD:12"
```

**Pricing Format:** `name:price:currency:duration_in_months`

#### 3. Subscribe User to Product

```bash
php bin/console app:subscribe user_63f2b1a4e8c9d product_63f2b1b5f7a8e   "Annual"
```

#### 4. List Active Subscriptions

```bash
php bin/console app:list-active-subscriptions user_63f2b1a4e8c9d
```

#### 5. Cancel a Subscription

```bash
php bin/console app:cancel-subscription sub_63f2b1c6g8b9f
```

## Testing

### Run All Tests

```bash
composer test
```
### Run Unit Tests Only

```bash
composer test-unit
```
### Run End-to-End Tests Only

```bash
composer test-e2e
```
