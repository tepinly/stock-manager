
# Laravel Stock Manager

The idea of the project is to manage product orders by managing their ingredients and fulfilling them based on the availability of each ingredient

## Setup

As a prerequisite, the project is built in Laravel and uses MySQL for the database. To get it up and running, run the following

### Setup database migrations

```cmd
php artisan migrate
```

### Seed the database

```cmd
php artisan db:seed
```

### Run the server

```cmd
php artisan serve
```

### For testing

```cmd
php artisan test
```

> [!CAUTION]
> Running tests resets the database, so make sure to re-seed it afterwards

## Abstract

The system consists of 3 models

- Product
- Ingredient
- Order

Each product consists of a number of ingredients in varying quantities, and multiple products can share the same ingredient.

Once an order comprised of a number of products has been placed, each product's ingredients is retrieved to calculate the consumption of the ingredients to fulfill the order.

Once an ingredient reaches below 50% of its total stock, the merchant receives a one-time email notification for said ingredient

## Assumptions

- Ingredient stock weights are calculated in grams within the database, making it easier to convert them to a higher unit when need be
- Ingredient maximum stock is calculated based on the last restock value. In the case of seeding, it's set to the initial stock value in the seed

## Design

The design of the database revolves around the 3 models mentioned above.

### Ingredient

- `name` - string
- `max_stock` - number
- `below_threshold` - number
- many-to-many relationship with `Product`

### Product

- `name` - string
- many-to-many relationship with `Order`
- many-to-many relationship with `Ingredient`

### Order

- many-to-many relationship with `Product`
- No attributes other than `id`. The table is used as a reference to products listed under it

## Pivot models

### Ingredient_Product

- `product_id` - id
- `ingredient_id` - id
- `weight` - Weight of the ingredient associated to the product

### Order_Product

- `order_id` - id
- `product_id` - id
- `quantity` - Quantity of the product corresponding to the order

# API

There is no frontend for the project, only the backend that runs on a `/api` prefix

## `POST /api/orders`

### Body

- `products` - array of
  - `product_id` - id
  - `quantity` - number

### Response

- `201` empty response

### Description

- Takes in the order body, validating the `product_id` of each product in the array
- Calculates the weight of ingredients necessary to fulfill the order
  - If there aren't enough ingredients in stock, the order will fail stating that there not enough ingredients
- Updates the ingredients in stock by subtracting the order's ingredients
  - If one ingredient falls below the `INGREDIENT_STOCK_THRESHOLD` constant threshold, an email notification will be sent to the `INGREDIENT_NOTIFICATION_EMAIL` address assigned in the environment
- The operation is atomic, ensuring that
  - order creation and ingredient updates are synchronized
  - emails are sent only after the database transaction is successful
