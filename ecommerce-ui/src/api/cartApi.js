import { request } from "./http";

const BASE_URL = import.meta.env.VITE_API_BASE_URL;

export const getCart = () => request(BASE_URL, "/cart").then((res) => res.data);

export const addToCart = (userId, productId, quantity) =>
  request(BASE_URL, "/cart", {
    method: "POST",
    body: JSON.stringify({ userId, productId, quantity }),
  }).then((res) => res.data);
