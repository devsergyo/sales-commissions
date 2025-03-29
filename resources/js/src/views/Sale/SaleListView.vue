<template>
  <div class="sale-list-view">
    <div class="page-header">
      <h1 class="page-title">Vendas</h1>
      <div class="page-actions">
        <RouterLink to="/vendas/nova" class="btn btn-primary">
          <i class="fa fa-plus"></i> Nova Venda
        </RouterLink>
      </div>
    </div>

    <div v-if="saleStore.error" class="alert alert-danger">
      {{ saleStore.error }}
    </div>

    <AppTable
      title="Lista de Vendas"
      :columns="tableColumns"
      :items="saleStore.sales"
      defaultSort="sale_date"
      defaultSortDir="desc"
    >
      <template #column-seller_id="{ item }">
        {{ sellerName(item) }}
      </template>
      
      <template #column-amount="{ value }">
        {{ formatCurrency(value) }}
      </template>
      
      <template #column-commission="{ value }">
        {{ formatCurrency(value) }}
      </template>
      
      <template #column-sale_date="{ value }">
        {{ formatDate(value) }}
      </template>

      <template #actions="{ item }">
        <button 
          class="btn btn-sm btn-outline-info" 
          @click="sendDailyReport(item.seller_id)"
          title="Enviar relatório diário"
        >
          <i class="fa fa-paper-plane"></i>
        </button>
      </template>

      <template #empty>
        <div class="empty-state">
          <i class="fa fa-shopping-cart fa-3x"></i>
          <p>Nenhuma venda encontrada</p>
        </div>
      </template>
    </AppTable>

    <div v-if="saleStore.sales.length > 0" class="summary-cards">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Total em Vendas</h5>
          <p class="card-value">{{ formatCurrency(totalAmount) }}</p>
        </div>
      </div>
      
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Total em Comissões</h5>
          <p class="card-value">{{ formatCurrency(totalCommission) }}</p>
        </div>
      </div>
      
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Média de Comissão</h5>
          <p class="card-value">{{ averageCommissionPercent }}%</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useSaleStore } from '../../stores/sale';
import type { Sale } from '../../interfaces/Sale';
import { useSellerStore } from '../../stores/seller';
import AppTable from '../../components/ui/AppTable.vue';

const saleStore = useSaleStore();
const sellerStore = useSellerStore();

const tableColumns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'seller_id', label: 'Vendedor', sortable: true },
  { key: 'amount', label: 'Valor', sortable: true },
  { key: 'commission', label: 'Comissão', sortable: true },
  { key: 'sale_date', label: 'Data', sortable: true },
];

const totalAmount = computed(() => {
  return saleStore.sales.reduce((sum: number, sale: Sale) => sum + Number(sale.amount), 0);
});

const totalCommission = computed(() => {
  return saleStore.sales.reduce((sum: number, sale: Sale) => sum + Number(sale.commission), 0);
});

const averageCommissionPercent = computed(() => {
  if (totalAmount.value === 0) return 0;
  return ((totalCommission.value / totalAmount.value) * 100).toFixed(2);
});

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
};

const formatDate = (dateString: string): string => {
  if (!dateString) return '';
  const dateObj = new Date(dateString);
  const day = dateObj.getUTCDate().toString().padStart(2, '0');
  const month = (dateObj.getUTCMonth() + 1).toString().padStart(2, '0');
  const year = dateObj.getUTCFullYear();
  return `${day}/${month}/${year}`;
};

const sellerName = (sale: Sale) => {
  if (sale.seller) {
    return sale.seller.fullname || `${sale.seller.first_name} ${sale.seller.last_name}`;
  }
  return `Vendedor ID: ${sale.seller_id}`;
};

const sendDailyReport = async (sellerId: number) => {
  try {
    await sellerStore.sendDailyReport(sellerId);
    alert('Relatório enviado com sucesso!');
  } catch (error) {
    alert(`Erro ao enviar relatório: ${error}`);
  }
};

onMounted(() => {
  console.log('SaleListView montado, buscando vendas...');
  saleStore.getAllSales();
});
</script>

<style scoped>
.sale-list-view {
  padding: var(--spacer-4);
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

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--spacer-4);
  margin-top: var(--spacer-4);
}

.card {
  background-color: white;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.card-body {
  padding: var(--spacer-4);
}

.card-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-gray-dark);
  margin-bottom: var(--spacer-2);
}

.card-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-primary-dark);
  margin: 0;
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

.alert {
  padding: 0.75rem 1.25rem;
  margin-bottom: 1rem;
  border-radius: 0.25rem;
}

.alert-danger {
  color: #721c24;
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
}
</style>
