<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import type { ApiResponse, LoginResponse } from '../../interfaces/ApiResponse';

const router = useRouter();
const authStore = useAuthStore();
const form = ref({
  email: 'testapi@tray.com.br',
  password: 'password'
});
const loading = ref(false);
const error = ref<string | null>(null);
const success = ref<string | null>(null);

onMounted(() => {
  console.log('LoginView montado');
  console.log('API Base URL:', import.meta.env.VITE_API_URL || 'http://localhost/api');
  if (authStore.isAuthenticated) {
    router.push('/dashboard');
  }
});

const handleSubmit = async () => {
  try {
    loading.value = true;
    error.value = null;
    success.value = null;
    
    if (!form.value.email || !form.value.password) {
      error.value = 'Por favor, preencha todos os campos';
      return;
    }

    console.log('Enviando requisição para:', `/auth/login`);
    const response = await authStore.login(form.value.email, form.value.password);
    
    if (response.success) {
      success.value = response.message || 'Login realizado com sucesso!';
      
      setTimeout(() => {
        router.push('/dashboard');
      }, 500);
    } else {
      error.value = response.message || 'Falha na autenticação. Verifique suas credenciais.';
    }
  } catch (e: any) {
    console.error('Erro completo:', e);
    error.value = e.message || 'Ocorreu um erro durante o login. Tente novamente mais tarde.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="login-container">
    <div class="login-card">
      <h1 class="title">Login</h1>
      
      <div v-if="error" class="alert alert-error">
        {{ error }}
      </div>
      
      <div v-if="success" class="alert alert-success">
        {{ success }}
      </div>
      
      <form @submit.prevent="handleSubmit" class="login-form">
        <div class="form-group">
          <label for="email">E-mail</label>
          <input 
            type="email" 
            id="email" 
            v-model="form.email" 
            placeholder="Digite seu e-mail" 
            autocomplete="email"
            required
          />
        </div>
        
        <div class="form-group">
          <label for="password">Senha</label>
          <input 
            type="password" 
            id="password" 
            v-model="form.password" 
            placeholder="Digite sua senha" 
            autocomplete="current-password"
            required
          />
        </div>
        
        <div class="form-actions">
          <button 
            type="submit" 
            class="btn-login" 
            :disabled="loading"
          >
            <span v-if="loading">Entrando...</span>
            <span v-else>Entrar</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
}

.login-card {
  width: 100%;
  max-width: 400px;
  padding: 2rem;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.title {
  margin-bottom: 1.5rem;
  color: #333;
  text-align: center;
  font-size: 1.8rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  font-weight: 500;
  color: #555;
}

input {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}

.btn-login {
  padding: 0.75rem;
  background-color: #4f46e5;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-login:hover {
  background-color: #4338ca;
}

.btn-login:disabled {
  background-color: #a5a5a5;
  cursor: not-allowed;
}

.form-actions {
  margin-top: 1rem;
}

.alert {
  padding: 0.75rem;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.alert-error {
  background-color: #fee2e2;
  color: #b91c1c;
  border: 1px solid #f87171;
}

.alert-success {
  background-color: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}
</style>
