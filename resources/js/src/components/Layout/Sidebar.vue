<template>
  <aside class="sidebar">
    <section class="sidebar-brand">
      <div class="sidebar-logo">
        <div class="logo-text">Sistema de Comissões</div>
      </div>
    </section>

    <nav class="sidebar-nav">
      <section class="nav-section">
        <h3 class="nav-title">Menu Principal</h3>
        <div class="nav-items">
          <RouterLink to="/dashboard" class="nav-item">
            <i class="nav-icon fa-solid fa-chart-line"></i>
            <span class="nav-text">Dashboard</span>
          </RouterLink>
          <RouterLink to="/vendedores" class="nav-item">
            <i class="nav-icon fa-solid fa-user-tie"></i>
            <span class="nav-text">Vendedores</span>
          </RouterLink>
          <RouterLink to="/vendas" class="nav-item">
            <i class="nav-icon fa-solid fa-user-tie"></i>
            <span class="nav-text">Vendas</span>
          </RouterLink>
        </div>
      </section>
    </nav>

    <UserProfile />
  </aside>
</template>

<script setup>
import UserProfile from './UserProfile.vue';
import { RouterLink } from 'vue-router';
</script>
