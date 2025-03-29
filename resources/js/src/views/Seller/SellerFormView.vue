<template>
  <section class="content">
    <div class="page-header">
      <h1 class="page-title">{{ isEdit ? 'Editar Vendedor' : 'Novo Vendedor' }}</h1>
      <div class="page-actions">
        <RouterLink to="/vendedores" class="btn btn-secondary">
          <i class="fa-solid fa-arrow-left"></i> Voltar
        </RouterLink>
      </div>
    </div>

    <form @submit.prevent="saveSeller" class="seller-form">
      <div class="form-row">
        <div class="form-group">
          <label for="first_name">Nome</label>
          <input
            type="text"
            id="first_name"
            v-model="form.first_name"
            class="form-control"
            required
          />
        </div>

        <div class="form-group">
          <label for="last_name">Sobrenome</label>
          <input
            type="text"
            id="last_name"
            v-model="form.last_name"
            class="form-control"
            required
          />
        </div>
      </div>

      <div class="form-group">
        <label for="email">E-mail</label>
        <input
          type="email"
          id="email"
          v-model="form.email"
          class="form-control"
          required
        />
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <i class="fa-solid fa-save"></i> {{ isEdit ? 'Atualizar' : 'Salvar' }}
        </button>
        <RouterLink to="/vendedores" class="btn btn-secondary">
          Cancelar
        </RouterLink>
      </div>

      <div v-if="showErrorMessage" class="alert alert-danger">
        {{ errorMessage }}
      </div>
    </form>

    <div class="loading" v-if="loading">
      <i class="fa-solid fa-circle-notch fa-spin"></i>
      <span>{{ isEdit ? 'Atualizando vendedor...' : 'Salvando vendedor...' }}</span>
    </div>
  </section>
</template>

<script setup lang="ts">
import { reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useSellerStore } from '../../stores/seller';
import { storeToRefs } from 'pinia';
import type { Seller } from '../../interfaces/Seller';

const route = useRoute();
const router = useRouter();
const sellerStore = useSellerStore();
const { loading, error, hasError, currentSeller } = storeToRefs(sellerStore);

const isEdit = computed(() => route.params.id !== undefined);
const sellerId = computed(() => isEdit.value ? Number(route.params.id) : null);

const form = reactive({
  first_name: '',
  last_name: '',
  email: ''
});

const errorMessage = computed(() => error.value || 'Erro ao processar solicitação');
const showErrorMessage = computed(() => hasError.value);

onMounted(async () => {
  if (isEdit.value && sellerId.value) {
    await sellerStore.getSellerById(sellerId.value);
    if (currentSeller.value) {
      form.first_name = currentSeller.value.first_name;
      form.last_name = currentSeller.value.last_name;
      form.email = currentSeller.value.email;
    }
  } else {
    sellerStore.clearCurrentSeller();
  }
});

const saveSeller = async () => {
  if (loading.value) return;

  let result: Seller | null = null;

  if (isEdit.value && sellerId.value) {
    result = await sellerStore.updateSeller(sellerId.value, form);
  } else {
    result = await sellerStore.createSeller(form);
  }

  if (result) {
    router.push('/vendedores');
  }
};
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacer-4);
}

.page-actions {
  display: flex;
  gap: var(--spacer-2);
}

.seller-form {
  background-color: white;
  border-radius: var(--border-radius-lg);
  padding: var(--spacer-5);
  box-shadow: var(--shadow-sm);
  max-width: 800px;
  margin: 0 auto;
}

.form-row {
  display: flex;
  gap: var(--spacer-3);
  margin-bottom: var(--spacer-3);
}

.form-row .form-group {
  flex: 1;
}

.form-group {
  margin-bottom: var(--spacer-3);
}

.form-group label {
  display: block;
  margin-bottom: var(--spacer-1);
  font-weight: 500;
  color: var(--color-gray-dark);
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--color-gray-light);
  border-radius: var(--border-radius-base);
  font-size: 1rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:focus {
  outline: none;
  border-color: var(--color-gold-base);
  box-shadow: 0 0 0 3px rgba(var(--color-gold-rgb), 0.15);
}

.form-actions {
  display: flex;
  gap: var(--spacer-3);
  margin-top: var(--spacer-4);
}

.alert {
  padding: var(--spacer-3);
  border-radius: var(--border-radius-base);
  margin-top: var(--spacer-3);
}

.alert-danger {
  background-color: rgba(220, 53, 69, 0.1);
  color: #dc3545;
  border: 1px solid rgba(220, 53, 69, 0.2);
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--spacer-5);
  gap: var(--spacer-3);
  color: var(--color-gray-base);
  margin-top: var(--spacer-4);
}

.loading i {
  font-size: 2rem;
  color: var(--color-gold-base);
}
</style>
