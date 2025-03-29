<template>
  <section class="content">
    <div class="page-header">
      <h1 class="page-title">Vendedores</h1>
      <div class="page-actions">
        <RouterLink to="/vendedores/novo" class="btn btn-primary">
          <i class="fa-solid fa-plus"></i> Novo Vendedor
        </RouterLink>
      </div>
    </div>

    <div class="error-alert" v-if="hasError">
      <i class="fa-solid fa-exclamation-triangle"></i>
      <span>{{ error }}</span>
    </div>

    <AppTable
      title="Lista de Vendedores"
      :columns="tableColumns"
      :items="sellers || []"
      defaultSort="first_name"
      defaultSortDir="asc"
    >
      <template #column-fullname="{ item }">
        {{ item && (item.fullname || `${item.first_name} ${item.last_name}`) }}
      </template>
      
      <template #actions="{ item }">
        <div class="action-buttons" v-if="item">
          <button class="btn btn-sm btn-info" @click="viewSellerSales(item)" title="Ver vendas">
            <i class="fa-solid fa-shopping-cart"></i>
            <span> Ver Vendas</span>
          </button>
          <button class="btn btn-sm btn-success" @click="sendReport(item.id)" title="Enviar relatório diário">
            <i class="fa-solid fa-paper-plane"></i>
            <span> Enviar Relatório</span>
          </button>
        </div>
      </template>

      <template #empty>
        <div class="empty-state">
          <i class="fa-solid fa-user-slash fa-3x"></i>
          <p>Nenhum vendedor cadastrado</p>
          <RouterLink to="/vendedores/novo" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Novo Vendedor
          </RouterLink>
        </div>
      </template>
    </AppTable>

    <div class="toast" v-if="toast.show">
      <div :class="['toast-content', toast.type]">
        <i :class="['fa-solid', toast.icon]"></i>
        <span>{{ toast.message }}</span>
      </div>
    </div>

    <div class="modal" v-if="sellerSalesModal.show">
      <div class="modal-overlay" @click="closeModal"></div>
      <div class="modal-container">
        <div class="modal-header">
          <h3>Vendas de {{ sellerSalesModal.sellerName }}</h3>
          <button class="modal-close" @click="closeModal">
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <div v-if="sellerSalesModal.loading" class="loading">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <p>Carregando vendas...</p>
          </div>
          <div v-else-if="sellerSalesModal.error" class="error-alert">
            <i class="fa-solid fa-exclamation-triangle"></i>
            <span>{{ sellerSalesModal.error }}</span>
          </div>
          <div v-else-if="sellerSalesModal.sales.length === 0" class="empty-state">
            <i class="fa-solid fa-shopping-cart fa-3x"></i>
            <p>Nenhuma venda encontrada para este vendedor</p>
          </div>
          <table v-else class="sales-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Valor</th>
                <th>Comissão</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="sale in sellerSalesModal.sales" :key="sale.id">
                <td>{{ sale.id }}</td>
                <td>{{ formatDate(sale.sale_date) }}</td>
                <td>{{ formatCurrency(sale.amount) }}</td>
                <td>{{ formatCurrency(sale.commission) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2" class="total-label">Total</td>
                <td>{{ formatCurrency(totalSalesAmount) }}</td>
                <td>{{ formatCurrency(totalSalesCommission) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { useSellerStore } from '../../stores/seller';
import { useSaleStore } from '../../stores/sale';
import { storeToRefs } from 'pinia';
import AppTable from '../../components/ui/AppTable.vue';
import type { Sale } from '../../interfaces/Sale';
import type { Seller } from '../../interfaces/Seller';
import { saleService } from '../../services/SaleService';

const sellerStore = useSellerStore();
const { sellers, error, hasError } = storeToRefs(sellerStore);

const tableColumns = [
  { key: 'fullname', label: 'Nome', sortable: true },
  { key: 'email', label: 'E-mail', sortable: true },
];

const toast = ref({
  show: false,
  message: '',
  type: 'success',
  icon: 'fa-check-circle'
});

const sellerSalesModal = ref({
  show: false,
  sellerId: 0,
  sellerName: '',
  sales: [] as Sale[],
  loading: false,
  error: ''
});

const totalSalesAmount = computed(() => {
  return sellerSalesModal.value.sales.reduce((sum, sale) => sum + Number(sale.amount), 0);
});

const totalSalesCommission = computed(() => {
  return sellerSalesModal.value.sales.reduce((sum, sale) => sum + Number(sale.commission), 0);
});

const formatCurrency = (value: number | string): string => {
  const numValue = typeof value === 'string' ? parseFloat(value) : value;
  return numValue.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const formatDate = (dateString: string): string => {
  if (!dateString) return '';
  const dateObj = new Date(dateString);
  const day = dateObj.getUTCDate().toString().padStart(2, '0');
  const month = (dateObj.getUTCMonth() + 1).toString().padStart(2, '0');
  const year = dateObj.getUTCFullYear();
  return `${day}/${month}/${year}`;
};

const showToast = (message: string, type: 'success' | 'error' = 'success') => {
  toast.value.show = true;
  toast.value.message = message;
  toast.value.type = type;
  toast.value.icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
  
  setTimeout(() => {
    toast.value.show = false;
  }, 3000);
};

const viewSellerSales = async (seller: Seller) => {
  sellerSalesModal.value.show = true;
  sellerSalesModal.value.sellerId = seller.id;
  sellerSalesModal.value.sellerName = seller.fullname || `${seller.first_name} ${seller.last_name}`;
  sellerSalesModal.value.loading = true;
  sellerSalesModal.value.error = '';
  sellerSalesModal.value.sales = [];
  
  try {
    const response = await saleService.getBySeller(seller.id);
    if (response.success && response.data) {
      sellerSalesModal.value.sales = response.data.sales;
    } else {
      sellerSalesModal.value.error = response.message || 'Erro ao carregar vendas';
    }
  } catch (err) {
    sellerSalesModal.value.error = 'Erro ao carregar vendas do vendedor';
  } finally {
    sellerSalesModal.value.loading = false;
  }
};

const closeModal = () => {
  sellerSalesModal.value.show = false;
};

const sendReport = async (sellerId: number) => {
  try {
    const result = await sellerStore.sendDailyReport(sellerId);
    if (result.success) {
      showToast('Relatório enviado com sucesso!', 'success');
    } else {
      showToast(result.message || 'Erro ao enviar relatório', 'error');
    }
  } catch (err) {
    showToast('Erro ao enviar relatório', 'error');
  }
};

onMounted(() => {
  sellerStore.getAllSellers();
});
</script>

<style scoped>
.content {
  padding: var(--spacer-4);
  width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacer-4);
}

.page-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--color-primary-dark);
  margin: 0;
}

.page-actions {
  display: flex;
  gap: var(--spacer-2);
}

.error-alert {
  background-color: var(--color-danger-light);
  color: var(--color-danger);
  padding: var(--spacer-3);
  border-radius: var(--border-radius);
  margin-bottom: var(--spacer-4);
  display: flex;
  align-items: center;
}

.error-alert i {
  margin-right: var(--spacer-2);
}

.action-buttons {
  display: flex;
  gap: var(--spacer-2);
  justify-content: flex-end;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: var(--spacer-2);
  padding: var(--spacer-4);
  color: var(--color-gray-light);
}

.empty-state i {
  opacity: 0.5;
  margin-bottom: var(--spacer-2);
}

.empty-state p {
  margin-bottom: var(--spacer-3);
  font-size: 1rem;
}

.toast {
  position: fixed;
  bottom: var(--spacer-4);
  right: var(--spacer-4);
  z-index: 1000;
}

.toast-content {
  display: flex;
  align-items: center;
  padding: var(--spacer-3);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-md);
  animation: slide-in 0.3s ease;
}

.toast-content.success {
  background-color: var(--color-success-light);
  color: var(--color-success);
}

.toast-content.error {
  background-color: var(--color-danger-light);
  color: var(--color-danger);
}

.toast-content i {
  margin-right: var(--spacer-2);
}

@keyframes slide-in {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  cursor: pointer;
}

.modal-container {
  background-color: white;
  border-radius: var(--border-radius);
  width: 80%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  z-index: 1051;
  box-shadow: var(--shadow-lg);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacer-3);
  border-bottom: 1px solid var(--color-border);
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: var(--color-primary-dark);
}

.modal-close {
  background: transparent;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: var(--color-gray);
}

.modal-body {
  padding: var(--spacer-4);
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--spacer-4);
}

.loading i {
  font-size: 2rem;
  color: var(--color-primary);
  margin-bottom: var(--spacer-2);
}

.sales-table {
  width: 100%;
  border-collapse: collapse;
}

.sales-table th, .sales-table td {
  padding: var(--spacer-2);
  text-align: left;
  border-bottom: 1px solid var(--color-border);
}

.sales-table th {
  background-color: var(--color-gray-lighter);
  font-weight: 600;
}

.sales-table tfoot td {
  font-weight: 600;
}

.total-label {
  text-align: right;
}
</style>