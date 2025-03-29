<template>
  <div class="app-table">
    <div v-if="title || $slots.actions" class="app-table__header">
      <h3 v-if="title" class="app-table__title">{{ title }}</h3>
      <div v-if="$slots.actions" class="app-table__actions">
        <slot name="actions"></slot>
      </div>
    </div>
    
    <div class="app-table__wrapper">
      <table class="app-table__table">
        <thead class="app-table__thead">
          <tr>
            <th 
              v-for="column in columns" 
              :key="column.key" 
              :class="[
                'app-table__th',
                { 'app-table__th--sortable': column.sortable },
                { 'app-table__th--sorted': sortKey === column.key }
              ]"
              @click="column.sortable ? sort(column.key) : null"
            >
              <div class="app-table__th-content">
                {{ column.label }}
                <span 
                  v-if="column.sortable" 
                  class="app-table__sort-icon"
                >
                  <i :class="[getSortIcon(column.key)]"></i>
                </span>
              </div>
            </th>
            <th v-if="$slots.actions" class="app-table__th app-table__th--actions">Ações</th>
          </tr>
        </thead>
        <tbody class="app-table__tbody">
          <template v-if="items.length">
            <tr 
              v-for="(item, index) in sortedItems" 
              :key="index"
              class="app-table__tr"
            >
              <td 
                v-for="column in columns" 
                :key="column.key"
                class="app-table__td"
              >
                <slot 
                  :name="`column-${column.key}`" 
                  :item="item" 
                  :value="item[column.key]"
                >
                  {{ formatValue(item[column.key], column.format) }}
                </slot>
              </td>
              <td v-if="$slots.actions" class="app-table__td app-table__td--actions">
                <slot name="actions" :item="item"></slot>
              </td>
            </tr>
          </template>
          <tr v-else class="app-table__tr app-table__tr--empty">
            <td :colspan="$slots.actions ? columns.length + 1 : columns.length" class="app-table__td app-table__td--empty">
              <slot name="empty">
                <div class="app-table__empty">
                  <i class="fa-solid fa-info-circle"></i>
                  <p>Nenhum registro encontrado</p>
                </div>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

interface Column {
  key: string
  label: string
  sortable?: boolean
  format?: (value: any) => string
}

interface Props {
  title?: string
  columns: Column[]
  items: any[]
  emptyText?: string
}

const props = withDefaults(defineProps<Props>(), {
  emptyText: 'Nenhum registro encontrado',
  items: () => []
})

const emit = defineEmits(['sort'])

const sortKey = ref('')
const sortDirection = ref('asc')

const sort = (key: string) => {
  if (sortKey.value === key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortDirection.value = 'asc'
  }
  
  emit('sort', { key: sortKey.value, direction: sortDirection.value })
}

const getSortIcon = (key: string) => {
  if (sortKey.value !== key) return 'fa-solid fa-sort'
  return sortDirection.value === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down'
}

const sortedItems = computed(() => {

  if (!sortKey.value || typeof props.items[0]?.[sortKey.value] === 'undefined') {
    return props.items
  }
  
  return [...props.items].sort((a, b) => {
    const aValue = a[sortKey.value]
    const bValue = b[sortKey.value]
    

    if (typeof aValue === 'string' && typeof bValue === 'string') {
      return sortDirection.value === 'asc'
        ? aValue.localeCompare(bValue)
        : bValue.localeCompare(aValue)
    }
    
    if (typeof aValue === 'number' && typeof bValue === 'number') {
      return sortDirection.value === 'asc'
        ? aValue - bValue
        : bValue - aValue
    }
    

    if (aValue instanceof Date && bValue instanceof Date) {
      return sortDirection.value === 'asc'
        ? aValue.getTime() - bValue.getTime()
        : bValue.getTime() - aValue.getTime()
    }

    if (typeof aValue === 'string' && typeof bValue === 'string') {
      const dateA = new Date(aValue)
      const dateB = new Date(bValue)
      if (!isNaN(dateA.getTime()) && !isNaN(dateB.getTime())) {
        return sortDirection.value === 'asc'
          ? dateA.getTime() - dateB.getTime()
          : dateB.getTime() - dateA.getTime()
      }
    }

    return sortDirection.value === 'asc'
      ? String(aValue).localeCompare(String(bValue))
      : String(bValue).localeCompare(String(aValue))
  })
})

const formatValue = (value: any, format?: (value: any) => string) => {
  if (value === null || value === undefined) {
    return '-'
  }

  if (typeof format === 'function') {
    return format(value)
  }

  if (typeof value === 'object') {
    if (value instanceof Date) {
      return value.toLocaleDateString()
    }
    return JSON.stringify(value)
  }

  if (typeof value === 'boolean') {
    return value ? 'Sim' : 'Não'
  }

  return String(value)
}
</script>

<style scoped>
.app-table {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  width: 100%;
  margin-bottom: 2rem;
}

.app-table__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--color-gray-lighter, #f0f0f0);
}

.app-table__title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--color-primary-dark, #2c3e50);
  margin: 0;
}

.app-table__wrapper {
  overflow-x: auto;
}

.app-table__table {
  width: 100%;
  border-collapse: collapse;
}

.app-table__thead {
  background-color: var(--color-primary-dark, #2c3e50);
  color: white;
}

.app-table__th {
  padding: 0.75rem 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.9rem;
  white-space: nowrap;
  position: relative;
}

.app-table__th--sortable {
  cursor: pointer;
}

.app-table__th--sortable:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.app-table__th--sorted {
  background-color: rgba(255, 255, 255, 0.15);
}

.app-table__th-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.app-table__sort-icon {
  opacity: 0.6;
  transition: opacity 0.2s ease;
}

.app-table__th--sorted .app-table__sort-icon {
  opacity: 1;
}

.app-table__th--actions {
  text-align: center;
  width: 1%;
  white-space: nowrap;
}

.app-table__tr {
  border-bottom: 1px solid var(--color-gray-lighter, #f0f0f0);
  transition: background-color 0.2s ease;
}

.app-table__tr:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.app-table__tr:last-child {
  border-bottom: none;
}

.app-table__td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  color: var(--color-gray-darker, #333);
}

.app-table__td--actions {
  text-align: center;
  width: 1%;
  white-space: nowrap;
}

.app-table__td--empty {
  text-align: center;
  padding: 3rem 1rem;
}

.app-table__empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: var(--color-gray-light, #999);
}

.app-table__empty-icon {
  font-size: 2rem;
  opacity: 0.5;
}

.app-table__empty p {
  margin: 0;
  font-size: 1rem;
}
</style>
