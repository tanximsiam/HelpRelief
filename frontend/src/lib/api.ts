import axios from "axios";

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE || "http://localhost:8000/api",
});

api.interceptors.request.use((cfg) => {
  const token = localStorage.getItem("hr_token");
  if (token) cfg.headers.Authorization = `Bearer ${token}`;
  return cfg;
});
