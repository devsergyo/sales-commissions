<script setup lang="ts">
import { useRouter } from 'vue-router';

const router = useRouter();

const goBack = () => {
  router.go(-1);
};

const goHome = () => {
  router.push('/');
};
</script>

<template>
  <div class="not-found-container">
    <div class="not-found-content">
      <h1>404</h1>
      <h2>Pu00e1gina nu00e3o encontrada</h2>
      <p>A pu00e1gina que vocu00ea estu00e1 procurando nu00e3o existe ou foi movida.</p>
      
      <div class="actions">
        <button @click="goBack" class="btn btn-back">Voltar</button>
        <button @click="goHome" class="btn btn-home">Ir para Inu00edcio</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.not-found-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
}

.not-found-content {
  text-align: center;
  padding: 2rem;
  max-width: 500px;
}

h1 {
  font-size: 6rem;
  margin: 0;
  color: #4f46e5;
}

h2 {
  font-size: 2rem;
  margin-top: 0;
  margin-bottom: 1rem;
  color: #333;
}

p {
  color: #666;
  margin-bottom: 2rem;
}

.actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-back {
  background-color: #e5e7eb;
  color: #4b5563;
}

.btn-back:hover {
  background-color: #d1d5db;
}

.btn-home {
  background-color: #4f46e5;
  color: white;
}

.btn-home:hover {
  background-color: #4338ca;
}
</style>
