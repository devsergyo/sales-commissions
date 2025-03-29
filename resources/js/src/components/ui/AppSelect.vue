<template>
  <div class="app-select">
    <label v-if="label" :for="id" class="app-select__label">
      {{ label }}
      <span v-if="required" class="app-select__required">*</span>
    </label>
    
    <div class="app-select__wrapper" @click="toggleDropdown" :class="{ 'app-select__wrapper--open': isOpen }">
      <div class="app-select__selected">
        <span v-if="selectedLabel" class="app-select__selected-text">{{ selectedLabel }}</span>
        <span v-else class="app-select__placeholder">{{ placeholder }}</span>
      </div>
      
      <div class="app-select__icon">
        <i :class="['fa-solid', isOpen ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
      </div>
      
      <div v-if="isOpen" class="app-select__dropdown">
        <div v-if="filterable" class="app-select__search">
          <input 
            ref="searchInput"
            type="text" 
            v-model="searchTerm" 
            class="app-select__search-input" 
            placeholder="Pesquisar..."
            @click.stop
          />
          <i class="fa-solid fa-search app-select__search-icon"></i>
        </div>
        
        <div class="app-select__options">
          <div 
            v-for="option in filteredOptions" 
            :key="option.value" 
            class="app-select__option"
            :class="{ 'app-select__option--selected': isSelected(option.value) }"
            @click.stop="selectOption(option)"
          >
            <i v-if="isSelected(option.value)" class="fa-solid fa-check app-select__check"></i>
            {{ option.label }}
          </div>
          
          <div v-if="filteredOptions.length === 0" class="app-select__no-results">
            Nenhum resultado encontrado
          </div>
        </div>
      </div>
    </div>
    
    <div v-if="error" class="app-select__error">{{ error }}</div>
    <div v-else-if="helpText" class="app-select__help">{{ helpText }}</div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'

interface Option {
  value: string | number
  label: string
}

interface Props {
  modelValue: string | number | string[] | number[] | null
  options: Option[]
  label?: string
  placeholder?: string
  disabled?: boolean
  required?: boolean
  multiple?: boolean
  filterable?: boolean
  helpText?: string
  error?: string
  id?: string
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  required: false,
  multiple: false,
  filterable: false,
  placeholder: 'Selecione'
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const searchTerm = ref('')
const searchInput = ref<HTMLInputElement | null>(null)

const selectedLabel = computed(() => {
  if (props.multiple && Array.isArray(props.modelValue)) {
    const selectedOptions = props.options
      .filter(option => {
        const modelValue = props.modelValue as (string[] | number[])
        // Verificar se o valor da opção existe na matriz de valores selecionados
        return modelValue.includes(option.value as never)
      })
      .map(option => option.label)
    
    if (selectedOptions.length === 0) return ''
    if (selectedOptions.length === 1) return selectedOptions[0]
    return `${selectedOptions.length} itens selecionados`
  }
  
  const selected = props.options.find(option => option.value === props.modelValue)
  return selected ? selected.label : ''
})

const filteredOptions = computed(() => {
  if (!searchTerm.value) return props.options
  
  const term = searchTerm.value.toLowerCase()
  return props.options.filter(option => 
    option.label.toLowerCase().includes(term)
  )
})

const toggleDropdown = () => {
  if (props.disabled) return
  
  isOpen.value = !isOpen.value
  
  if (isOpen.value && props.filterable) {
    nextTick(() => {
      if (searchInput.value) {
        searchInput.value.focus()
      }
    })
  } else {
    searchTerm.value = ''
  }
}

const selectOption = (option: Option) => {
  if (props.multiple && Array.isArray(props.modelValue)) {
    const newValue = [...props.modelValue]
    const index = newValue.indexOf(option.value as never)
    
    if (index === -1) {
      newValue.push(option.value as never)
    } else {
      newValue.splice(index, 1)
    }
    
    emit('update:modelValue', newValue)
  } else {
    emit('update:modelValue', option.value)
    isOpen.value = false
  }
}

const isSelected = (value: string | number) => {
  if (props.multiple && Array.isArray(props.modelValue)) {
    const modelValue = props.modelValue as (string[] | number[])
    return modelValue.includes(value as never)
  }
  
  return props.modelValue === value
}

const closeDropdown = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.app-select')) {
    isOpen.value = false
    searchTerm.value = ''
  }
}

onMounted(() => {
  document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
})

watch(() => props.modelValue, () => {
  if (props.multiple && !Array.isArray(props.modelValue)) {
    emit('update:modelValue', [])
  }
})
</script>

<style scoped>
.app-select {
  display: flex;
  flex-direction: column;
  margin-bottom: var(--spacer-3);
  position: relative;
  width: 100%;
}

.app-select__label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: var(--spacer-1);
  color: var(--color-gray-dark);
}

.app-select__required {
  color: var(--color-danger);
  margin-left: var(--spacer-1);
}

.app-select__wrapper {
  position: relative;
  cursor: pointer;
  background-color: white;
  border: 1px solid var(--color-gray-light);
  border-radius: var(--border-radius);
  transition: all 0.2s ease;
}

.app-select__wrapper:hover {
  border-color: var(--color-primary-light);
}

.app-select__wrapper--open {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px var(--color-primary-faintest);
}

.app-select__selected {
  padding: var(--spacer-2) var(--spacer-3);
  padding-right: calc(var(--spacer-3) * 2);
  font-size: 0.875rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.app-select__selected-text {
  color: var(--color-gray-darkest);
}

.app-select__placeholder {
  color: var(--color-gray-base);
}

.app-select__icon {
  position: absolute;
  top: 50%;
  right: var(--spacer-2);
  transform: translateY(-50%);
  color: var(--color-gray-base);
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  pointer-events: none;
}

.app-select__dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 10;
  background-color: white;
  border-radius: 0 0 var(--border-radius) var(--border-radius);
  box-shadow: var(--shadow-md);
  margin-top: var(--spacer-1);
  overflow: hidden;
  max-height: 300px;
  display: flex;
  flex-direction: column;
}

.app-select__search {
  position: relative;
  padding: var(--spacer-2);
  border-bottom: 1px solid var(--color-gray-lightest);
}

.app-select__search-input {
  width: 100%;
  padding: var(--spacer-2);
  padding-left: calc(var(--spacer-2) * 2);
  font-size: 0.875rem;
  border: 1px solid var(--color-gray-lighter);
  border-radius: var(--border-radius);
  outline: none;
}

.app-select__search-input:focus {
  border-color: var(--color-primary-light);
}

.app-select__search-icon {
  position: absolute;
  left: calc(var(--spacer-2) + var(--spacer-1));
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-gray-base);
  font-size: 0.75rem;
  pointer-events: none;
}

.app-select__options {
  flex: 1;
  overflow-y: auto;
}

.app-select__option {
  padding: var(--spacer-2) var(--spacer-3);
  font-size: 0.875rem;
  color: var(--color-gray-darkest);
  transition: background 0.15s ease;
  display: flex;
  align-items: center;
}

.app-select__option:hover {
  background-color: var(--color-gray-faintest);
}

.app-select__option--selected {
  background-color: var(--color-primary-faintest);
  color: var(--color-primary-dark);
  font-weight: 500;
}

.app-select__option--selected:hover {
  background-color: var(--color-primary-faintest);
}

.app-select__check {
  color: var(--color-primary);
  margin-right: var(--spacer-2);
  font-size: 0.75rem;
}

.app-select__no-results {
  padding: var(--spacer-3);
  text-align: center;
  color: var(--color-gray-base);
  font-size: 0.875rem;
}

.app-select__error {
  margin-top: var(--spacer-1);
  font-size: 0.75rem;
  color: var(--color-danger);
}

.app-select__help {
  margin-top: var(--spacer-1);
  font-size: 0.75rem;
  color: var(--color-gray-base);
}
</style>
