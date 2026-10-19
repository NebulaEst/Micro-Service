# Review Service

Flask and MongoDB review API for the e-commerce microservices repository.
The full project is public at [NebulaEst/Micro-Service](https://github.com/NebulaEst/Micro-Service).

## Run the services

The root `docker-compose.yml` is the only Compose file. From the repository
root, start all three containers and wait for the databases to become healthy:

```sh
docker compose up --build
```

The API is available at `http://localhost:3002`; the existing product service
is available at `http://localhost:3000`. For local Flask development, copy
`.env.example` to `.env`, run `python -m pip install -r requirements.txt`, and
then run `python run.py`. Start `review-db`, `product-db`, and `product-service`
with Docker Compose first.

## Endpoints

| Method | Path | Success |
| --- | --- | --- |
| GET | `/health` | `200` health status |
| GET | `/reviews/product/{product_id}` | `200` reviews and rating summary |
| GET | `/reviews/{id}` | `200` one review |
| POST | `/reviews` | `201` created review |
| PUT | `/reviews/{id}` | `200` updated review |
| DELETE | `/reviews/{id}` | `200` deletion confirmation |

Each review has an ObjectId string `id`, integer `product_id`, `user_id`, and
`rating`, a string `comment`, and ISO-8601 `created_at` and `updated_at`
timestamps. Each user can review a product once. Reviews are ordered newest
first, and the product listing also returns `average_rating` and
`total_reviews`.

### Validation and error behavior

- `product_id` and `user_id` must be integer values of at least 1.
- `rating` must be an integer from 1 through 5; booleans are not accepted.
- `comment` is optional (defaults to an empty string), must be text, and is
  limited to 1000 characters.
- Invalid input returns `422` with all field errors. Missing products also
  return `422`; a duplicate user/product review returns `409`.
- A missing or malformed review ID returns `404`. Unknown paths return JSON
  `404`; unsupported methods return JSON `405`.
- If product-service cannot be reached or responds with an error, creating a
  review returns `503`. Review reads remain available without product-service.

### Example requests

```sh
curl http://localhost:3002/health

curl -X POST http://localhost:3002/reviews \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"user_id":7,"rating":5,"comment":"Bagus dan sesuai deskripsi"}'

curl http://localhost:3002/reviews/product/1
curl http://localhost:3002/reviews/665f1c2e9a1b2c3d4e5f6a7b

curl -X PUT http://localhost:3002/reviews/665f1c2e9a1b2c3d4e5f6a7b \
  -H "Content-Type: application/json" \
  -d '{"rating":4,"comment":"Setelah dipakai, masih awet"}'

curl -X DELETE http://localhost:3002/reviews/665f1c2e9a1b2c3d4e5f6a7b

# Invalid rating and required fields return 422 with per-field details.
curl -i -X POST http://localhost:3002/reviews \
  -H "Content-Type: application/json" \
  -d '{"product_id":0,"user_id":true,"rating":6,"comment":null}'

# A non-existent product returns 422; a repeated user/product pair returns 409.
curl -i -X POST http://localhost:3002/reviews \
  -H "Content-Type: application/json" \
  -d '{"product_id":999,"user_id":7,"rating":5}'

# Invalid ID returns 404; an unsupported method returns 405.
curl -i http://localhost:3002/reviews/abc
curl -i -X PATCH http://localhost:3002/reviews
```

Run the API and validation tests without Docker or a live MongoDB:

```sh
python -m unittest discover -s tests -v
```
