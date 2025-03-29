<template>
  <section class="user-profile">
    <div class="user-info">
      <div class="user-avatar">
        <i class="fa-solid fa-user-doctor"></i>
      </div>
      <div class="user-details">
        <h4 class="user-name">{{ user?.name || 'Usuário' }}</h4>
        <p class="user-email"><i class="fa-solid fa-envelope"></i> {{ user?.email || 'usuario@exemplo.com' }}</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { inject, computed } from 'vue';

const authStore = inject('authStore');
const user = computed(() => authStore.user);
</script>

<style scoped>
.user-profile {
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.2);
  color: #000000;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  margin-right: 0.75rem;
}

.user-details {
  flex-grow: 1;
  overflow: hidden;
}

.user-name {
  font-size: 0.9rem;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin: 0 0 0.25rem 0;
  color: #000000;
}

.user-email {
  font-size: 0.7rem;
  color: #000000;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.user-email i {
  color: #fff;
}
</style>
