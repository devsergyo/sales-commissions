<template>
    <nav class="breadcrumb" aria-label="Navegação hierárquica">
      <ol class="breadcrumb-list">
        <li class="breadcrumb-item">
          <RouterLink to="/" class="breadcrumb-link">
            <i class="fa-solid fa-home"></i>
            <span>Início</span>
          </RouterLink>
        </li>
        <li class="breadcrumb-item active">
          <i class="fa-solid fa-chevron-right separator"></i>
          <span>Dashboard</span>
        </li>
      </ol>
    </nav>
  </template>
  
  <script setup>
  import { RouterLink } from 'vue-router';
  </script>
  