<template>
  <header class="header">
    <Breadcrumb />

    <div class="header-actions">
      <button class="header-button" aria-label="Pesquisar">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>
      <button class="header-button" aria-label="Notificações">
        <i class="fa-solid fa-bell"></i>
      </button>
      <button class="header-button" aria-label="Usuário">
        <i class="fa-solid fa-user-circle"></i>
      </button>
      <button 
        class="header-button logout-button" 
        aria-label="Sair" 
        @click="handleLogout"
        title="Sair do sistema"
      >
        <i class="fa-solid fa-sign-out-alt"></i>
      </button>
      <button class="header-button toggle-sidebar" aria-label="Menu">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>
  </header>
</template>

<script setup>
import Breadcrumb from './Breadcrumb.vue';
import { useAuthStore } from '../../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = async () => {
  try {
    await authStore.logout();
    router.push('/login');
  } catch (error) {
    console.error('Erro ao fazer logout:', error);
  }
};
</script>

<style scoped>
.header {
  background-color: #fff;
  border-bottom: 1px solid #e9ecef;
  padding: 0.75rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 60px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.header-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.25rem;
  color: #6c757d;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s, color 0.3s;
}

.header-button:hover {
  background-color: #f8f9fa;
  color: #212529;
}

.header-button:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
}

.toggle-sidebar {
  display: none;
}

.logout-button {
  color: #dc3545;
}

.logout-button:hover {
  background-color: #f8d7da;
  color: #dc3545;
}

@media (max-width: 768px) {
  .toggle-sidebar {
    display: flex;
  }
}
</style>