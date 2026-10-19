import unittest
from datetime import datetime, timezone
from unittest.mock import patch

from pymongo.errors import DuplicateKeyError

from app.exceptions import (
    DuplicateReviewError,
    ProductNotFoundError,
    ReviewNotFoundError,
)
from app.services import review_service

REVIEW_ID = "665f1c2e9a1b2c3d4e5f6a7b"


class ReviewServiceTests(unittest.TestCase):
    def setUp(self):
        self.review_data = {
            "product_id": 1,
            "user_id": 7,
            "rating": 5,
            "comment": "Bagus",
        }

    @patch("app.services.review_service.review_model.insert")
    @patch("app.services.review_service.product_client.product_exists")
    def test_create_review_checks_product_and_sets_utc_timestamps(
        self, product_exists, insert
    ):
        product_exists.return_value = True
        insert.return_value = {"id": REVIEW_ID}

        result = review_service.create_review(self.review_data)

        self.assertEqual(result, {"id": REVIEW_ID})
        product_exists.assert_called_once_with(1)
        inserted_document = insert.call_args.args[0]
        self.assertEqual(
            {key: inserted_document[key] for key in self.review_data},
            self.review_data,
        )
        self.assertIsInstance(inserted_document["created_at"], datetime)
        self.assertEqual(inserted_document["created_at"].tzinfo, timezone.utc)
        self.assertEqual(inserted_document["created_at"], inserted_document["updated_at"])

    @patch("app.services.review_service.review_model.insert")
    @patch("app.services.review_service.product_client.product_exists", return_value=False)
    def test_missing_product_is_not_saved(self, product_exists, insert):
        with self.assertRaises(ProductNotFoundError):
            review_service.create_review(self.review_data)

        product_exists.assert_called_once_with(1)
        insert.assert_not_called()

    @patch(
        "app.services.review_service.review_model.insert",
        side_effect=DuplicateKeyError("duplicate"),
    )
    @patch("app.services.review_service.product_client.product_exists", return_value=True)
    def test_duplicate_index_error_becomes_duplicate_review_error(
        self, _product_exists, _insert
    ):
        with self.assertRaises(DuplicateReviewError):
            review_service.create_review(self.review_data)

    @patch("app.services.review_service.review_model.find_by_id")
    def test_invalid_object_id_is_a_not_found_error(self, find_by_id):
        with self.assertRaises(ReviewNotFoundError):
            review_service.get_review("not-an-object-id")

        find_by_id.assert_not_called()


if __name__ == "__main__":
    unittest.main()
