import { useAuthStore } from '../stores/auth';

export function useAuth() {
  const authStore = useAuthStore();
  
  return {
    isAuthenticated: authStore.isAuthenticated,
    user: authStore.user,
    login: authStore.login,
    logout: authStore.logout,
    register: authStore.register,
    loadUserFromStorage: authStore.loadUserFromStorage
  };
}
