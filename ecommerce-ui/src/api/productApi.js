import { request } from "./http";

const BASE_URL = import.meta.env.VITE_PRODUCT_URL;

export const getCart = () =>
  request(BASE_URL, `/carts/${userId}`).then((res) => res.data);

export const getProductById = (userId, productId, quantity) =>
  request(BASE_URL, "/carts", {
    method: "POST",
    body: JSON.stringify({ userId, productId, quantity }),
  }).then((res) => res.data);

export const updateCartItem = (itemId, quantity) =>
  request(BASE_URL, `/carts/items/${itemId}`, {
    method: "PUT",
    body: { quantity },
  }).then((res) => res.data);

export const removeCartItem = (itemId) =>
  request(BASE_URL, `/carts/items/${itemId}`, {
    method: "DELETE",
  }).then((res) => res.data);

export const clearCart = (userId) =>
  request(BASE_URL, `/carts/${userId}`, {
    method: "DELETE",
  }).then((res) => res.data);
