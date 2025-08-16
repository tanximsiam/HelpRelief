import { defineStore } from "pinia";
import { api } from "@/lib/api";
import router from "@/router";

type User = {
  id: number;
  name: string;
  email: string;
  role: "general" | "ngo_staff" | "admin";
  volunteer?: boolean;
  ngo_id?: number | null;
};

export const useAuth = defineStore("auth", {
  state: () => ({
    user: null as User | null,
    token: localStorage.getItem("hr_token") || "",
    loading: false,
  }),
  getters: {
    isAuthenticated: (s) => !!s.token && !!s.user,
    role: (s) => s.user?.role,
    isAdmin(): boolean { return this.user?.role === "admin"; },
    isNGO(): boolean { return this.user?.role === "ngo_staff"; },
    isGeneral(): boolean { return this.user?.role === "general"; },
  },
  actions: {
    async login(payload: { email: string; password: string }) {
      this.loading = true;
      try {
        const { data } = await api.post("/login", payload);
        // expect { token, user }
        this.token = data.token;
        localStorage.setItem("hr_token", this.token);
        this.user = data.user as User;
      } finally {
        this.loading = false;
      }
    },
    async fetchUser() {
      if (!this.token) return;
      const { data } = await api.get("/user");
      this.user = data as User;
    },
    async logout() {
      try { await api.post("/logout"); } catch {}
      this.token = "";
      this.user = null;
      localStorage.removeItem("hr_token");
      router.replace({ name: 'home' })
    },
  },
});
