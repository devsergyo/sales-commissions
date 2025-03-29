<template>
  <div id="app">
    <div class="app-layout" v-if="authStore.isAuthenticated">
      <Sidebar />
      <main class="main-content">
        <Header />
        <router-view />
      </main>
    </div>
    <div v-else>
      <router-view />
    </div>
  </div>
</template>

<script setup>
import { onMounted, provide } from 'vue';
import { useAuthStore } from './stores/auth';
import Sidebar from './components/Layout/Sidebar.vue';
import Header from './components/Layout/Header.vue';

const authStore = useAuthStore();

onMounted(() => {
  if (!authStore.isAuthenticated) {
    authStore.loadUserFromStorage();
  }
});

provide('authStore', authStore);
</script>

<style>
@import './assets/css/main.css';
@import './assets/css/components.css';

.main-content {
  margin-left: var(--sidebar-width);
  padding-top: var(--header-height);
  min-height: 100vh;
  background-color: #f5f7fa;
}
</style>
