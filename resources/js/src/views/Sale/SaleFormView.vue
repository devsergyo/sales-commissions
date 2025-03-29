<template>
  <section class="content">
    <div class="page-header">
      <h1 class="page-title">Nova Venda</h1>
      <div class="page-actions">

      </div>
    </div>

    <div class="error-alert" v-if="hasError">
      <i class="fa-solid fa-exclamation-triangle"></i>
      <span>{{ error }}</span>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Dados da Venda</h2>
        <div class="card-subtitle">Preencha os dados da venda realizada</div>
      </div>

      <div class="card-body">
        <form @submit.prevent="handleSubmit" class="sale-form">
          <div class="form-row">
            <div class="form-column">
              <AppSelect
                v-model="form.seller_id"
                :options="sellerOptions"
                label="Vendedor"
                placeholder="Selecione um vendedor"
                :disabled="loadingSellers"
                required
              >
                <template #default v-if="loadingSellers">
                  <div class="loading-indicator">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                    Carregando vendedores...
                  </div>
                </template>
              </AppSelect>
            </div>

            <div class="form-column">
              <div class="form-group">
                <label for="sale_date">Data da Venda</label>
                <div class="input-icon-wrapper">
                  <input 
                    type="date" 
                    id="sale_date" 
                    v-model="form.sale_date" 
                    required
                    class="input-with-icon"
                  >
                  <i class="fa-solid fa-calendar input-icon"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-column">
              <div class="form-group">
                <label for="amount">Valor da Venda (R$)</label>
                <div class="input-icon-wrapper">
                  <input 
                    type="text" 
                    id="amount" 
                    v-model="form.amount"
                    required
                    class="input-with-icon"
                  >
                  <i class="fa-solid fa-dollar-sign input-icon"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="form-divider">
            <hr>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-check"></i> Salvar Venda
            </button>
            <RouterLink to="/vendas" class="btn btn-secondary">
              <i class="fa-solid fa-arrow-left"></i> Voltar
            </RouterLink>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref, computed, nextTick } from 'vue';
import { useSaleStore } from '../../stores/sale';
import { useSellerStore } from '../../stores/seller';
import { storeToRefs } from 'pinia';
import { useRouter } from 'vue-router';
import AppSelect from '../../components/ui/AppSelect.vue';

const router = useRouter();
const saleStore = useSaleStore();
const sellerStore = useSellerStore();
const { error, hasError } = storeToRefs(saleStore);
const { sellers, loading: loadingSellers } = storeToRefs(sellerStore);

const form = ref({
  seller_id: '',
  amount: '',
  sale_date: new Date().toISOString().split('T')[0]
});

const parseMoneyValue = (value: string): number => {
  if (!value) return 0;
  // Se o valor estiver sem formatação, apenas converte para número
  if (!isNaN(Number(value))) {
    return Number(value);
  }
  // Remove todos os caracteres que não são números
  const numericValue = value.replace(/[^0-9]/g, '');
  return parseFloat((parseInt(numericValue || '0') / 100).toFixed(2));
};

const sellerOptions = computed(() => {
  return (sellers.value || []).map(seller => ({
    value: seller.id.toString(),
    label: seller.fullname || `${seller.first_name} ${seller.last_name}`
  }));
});

const handleSubmit = async () => {
  try {
    if (!form.value.seller_id) {
      alert('Por favor, selecione um vendedor');
      return;
    }

    const amountValue = parseMoneyValue(form.value.amount);
    if (amountValue <= 0) {
      alert('Por favor, informe um valor de venda válido');
      return;
    }
    
    const result = await saleStore.createSale({
      seller_id: parseInt(form.value.seller_id as string),
      amount: amountValue,
      sale_date: new Date(form.value.sale_date)
    });

    if (result.success) {
      nextTick(() => {
        router.push('/vendas');
      });
    } else {
      alert(result.message || 'Erro ao criar venda');
    }
  } catch (error) {
    console.error('Erro ao processar venda:', error);
    alert('Ocorreu um erro ao processar a venda');
  }
};

onMounted(() => {
  sellerStore.getAllSellers();
});
</script>

<style scoped>
.card {
  background-color: white;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
  margin-bottom: var(--spacer-5);
  overflow: hidden;
}

.card-header {
  padding: var(--spacer-4);
  border-bottom: 1px solid var(--color-gray-light);
  background: linear-gradient(to right, var(--color-gray-lighter), white);
}

.card-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--color-gray-dark);
}

.card-subtitle {
  margin-top: var(--spacer-1);
  color: var(--color-gray-base);
  font-size: 0.875rem;
}

.card-body {
  padding: var(--spacer-4);
}

.sale-form {
  display: flex;
  flex-direction: column;
  gap: var(--spacer-4);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacer-4);
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}

.form-column {
  display: flex;
  flex-direction: column;
}

.form-group {
  margin-bottom: var(--spacer-3);
}

.form-group label {
  display: block;
  margin-bottom: var(--spacer-2);
  font-weight: 500;
  color: var(--color-gray-dark);
}

.input-icon-wrapper {
  position: relative;
}

.input-with-icon {
  padding: 0.625rem 1rem 0.625rem 2.5rem;
  font-size: 1rem;
  border: 1px solid var(--color-gray-light);
  border-radius: var(--border-radius-base);
  width: 100%;
  transition: all 0.3s ease;
}

.input-with-icon:focus {
  outline: none;
  border-color: var(--color-gold-base);
  box-shadow: 0 0 0 2px rgba(197, 165, 114, 0.2);
}

.input-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-gray-base);
}

.form-divider {
  margin: var(--spacer-3) 0;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  padding-top: var(--spacer-3);
}

.loading-indicator {
  display: flex;
  align-items: center;
  gap: var(--spacer-2);
  padding: var(--spacer-2);
  color: var(--color-gray-base);
  font-style: italic;
}

.error-alert {
  background-color: #f8d7da;
  color: #721c24;
  padding: var(--spacer-3);
  border-radius: var(--border-radius-base);
  margin-bottom: var(--spacer-4);
  display: flex;
  align-items: center;
  gap: var(--spacer-2);
}
</style>
