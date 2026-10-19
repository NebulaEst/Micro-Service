import unittest
from unittest.mock import patch

import requests

from app.clients import product_client
from app.exceptions import ProductServiceUnavailableError


class ProductClientTests(unittest.TestCase):
    @patch.dict("os.environ", {"PRODUCT_SERVICE_URL": "http://products:3000/"})
    @patch("app.clients.product_client.requests.get")
    def test_existing_product(self, get):
        get.return_value.status_code = 200
        get.return_value.ok = True

        self.assertTrue(product_client.product_exists(12))
        get.assert_called_once_with(
            "http://products:3000/products/12",
            timeout=product_client.TIMEOUT_SECONDS,
        )

    @patch("app.clients.product_client.requests.get")
    def test_missing_product(self, get):
        get.return_value.status_code = 404

        self.assertFalse(product_client.product_exists(12))

    @patch("app.clients.product_client.requests.get")
    def test_network_failure_is_wrapped_with_its_cause(self, get):
        timeout = requests.Timeout("request timed out")
        get.side_effect = timeout

        with self.assertRaises(ProductServiceUnavailableError) as raised:
            product_client.product_exists(12)

        self.assertIs(raised.exception.__cause__, timeout)

    @patch("app.clients.product_client.requests.get")
    def test_product_service_error_status_is_unavailable(self, get):
        get.return_value.status_code = 500
        get.return_value.ok = False

        with self.assertRaises(ProductServiceUnavailableError):
            product_client.product_exists(12)


if __name__ == "__main__":
    unittest.main()
