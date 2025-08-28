<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { api } from "@/lib/api";
import TertiaryButton from './TertiaryButton.vue';

// Props
interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  volunteer?: boolean;
  ngo_id?: number | null;
}

interface Ngo {
  id?: number;
  name?: string;
  email?: string;
  phone?: string;
  address?: string;
  type?: string;
  registration_number?: string;
  registration_no?: string;
  description?: string;
  website?: string;
  established_date?: string;
  established_year?: number;
  based_in?: string;
  director_name?: string;
  director_phone?: string;
  num_employees?: number;
  logo_url?: string;
}

const props = defineProps<{
  user?: User | null;
  ngo?: Ngo | null;
  isNgoEdit?: boolean;
}>();

// Emits
const emit = defineEmits<{
  close: [];
  profileUpdated: [];
}>();

// User form data interface (from UserProfileUpdate.vue)
interface UserFormData {
  name: string;
  email: string;
  phone: string;
  password: string;
  password_confirmation: string;
  current_password: string;
}

// NGO form data interface (from NgoProfileUpdate.vue)
interface NgoFormData {
  name: string;
  description: string;
  phone: string;
  based_in: string;
  website: string;
  director_name: string;
  director_phone: string;
  current_password: string;
}

// State
const userFormData = ref<UserFormData>({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  current_password: '',
});

const ngoFormData = ref<NgoFormData>({
  name: '',
  description: '',
  phone: '',
  based_in: '',
  website: '',
  director_name: '',
  director_phone: '',
  current_password: '',
});

const originalUserData = ref<UserFormData>({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  current_password: '',
});

const originalNgoData = ref<NgoFormData>({
  name: '',
  description: '',
  phone: '',
  based_in: '',
  website: '',
  director_name: '',
  director_phone: '',
  current_password: '',
});

const userRole = ref<string>('');
const successMessage = ref<string>('');
const errorMessage = ref<string>('');
const validationErrors = ref<Record<string, string[]>>({});

// Computed property to check if update button should be enabled
const isUpdateEnabled = computed(() => {
  if (props.isNgoEdit) {
    return ngoFormData.value.current_password.trim() !== '';
  } else {
    return userFormData.value.current_password.trim() !== '';
  }
});

// Form field component data
const userFields = computed(() => [
  { id: 'name', label: 'Name', type: 'text', model: 'name', placeholder: originalUserData.value.name || 'Enter your name' },
  { id: 'phone', label: 'Phone', type: 'text', model: 'phone', placeholder: originalUserData.value.phone || 'Enter your phone number' },
  { id: 'password', label: 'Password (leave blank if no change)', type: 'password', model: 'password', placeholder: '' },
  { id: 'password_confirmation', label: 'Confirm Password', type: 'password', model: 'password_confirmation', placeholder: '' }
]);

const ngoFieldsFullWidth = computed(() => [
  { id: 'ngo_name', label: 'Name', type: 'text', model: 'name', placeholder: originalNgoData.value.name || 'Enter organization name' },
  { id: 'description', label: 'Description', type: 'textarea', model: 'description', placeholder: originalNgoData.value.description || 'Enter organization description' }
]);

const ngoFieldsGrid = computed(() => [
  { id: 'ngo_phone', label: 'Phone', type: 'text', model: 'phone', placeholder: originalNgoData.value.phone || 'Enter phone number' },
  { id: 'based_in', label: 'Based In (Location)', type: 'text', model: 'based_in', placeholder: originalNgoData.value.based_in || 'Enter location' },
  { id: 'website', label: 'Website', type: 'url', model: 'website', placeholder: originalNgoData.value.website || 'Enter website URL' },
  { id: 'director_name', label: 'Director Name', type: 'text', model: 'director_name', placeholder: originalNgoData.value.director_name || 'Enter director name' },
  { id: 'director_phone', label: 'Director Phone', type: 'text', model: 'director_phone', placeholder: originalNgoData.value.director_phone || 'Enter director phone' }
]);

// Initialize data on mount and when props change
const initializeData = async () => {
  try {
    // Reset messages
    successMessage.value = '';
    errorMessage.value = '';
    validationErrors.value = {};

    if (props.isNgoEdit && props.ngo?.id) {
      // Fetch NGO data
      const response = await api.get(`/ngo/${props.ngo.id}`);
      const ngoData = response.data;

      ngoFormData.value = {
        name: ngoData.name || '',
        description: ngoData.description || '',
        phone: ngoData.phone || '',
        based_in: ngoData.based_in || '',
        website: ngoData.website || '',
        director_name: ngoData.director_name || '',
        director_phone: ngoData.director_phone || '',
        current_password: '',
      };
      originalNgoData.value = { ...ngoFormData.value };
      console.log('NGO form data initialized:', ngoFormData.value);
    } else {
      // Fetch user data
      const response = await api.get('/user');
      const userData = response.data;

      userFormData.value = {
        name: userData.name || '',
        email: userData.email || '',
        phone: userData.phone || '',
        password: '',
        password_confirmation: '',
        current_password: '',
      };
      originalUserData.value = { ...userFormData.value };
      userRole.value = userData.role || '';
      console.log('User form data initialized:', userFormData.value);
    }
  } catch (error) {
    console.error('Error loading profile data:', error);
    errorMessage.value = 'Failed to load profile data.';
  }
};

onMounted(initializeData);
watch([() => props.isNgoEdit, () => props.ngo?.id], initializeData);

// Handle user profile submission (from UserProfileUpdate.vue)
const handleUserSubmit = async () => {
  errorMessage.value = '';
  validationErrors.value = {};
  successMessage.value = '';

  // Require current password for any update
  if (!userFormData.value.current_password) {
    errorMessage.value = 'Current password is required to update your profile.';
    return;
  }

  // Prepare data to send: include current password and changed fields
  const updatedData: Partial<UserFormData> = {
    current_password: userFormData.value.current_password
  };

  if (userFormData.value.name !== originalUserData.value.name) {
    updatedData.name = userFormData.value.name;
  }
  if (userFormData.value.phone !== originalUserData.value.phone) {
    updatedData.phone = userFormData.value.phone;
  }
  if (userFormData.value.password) {
    updatedData.password = userFormData.value.password;
    if (userFormData.value.password_confirmation) {
      updatedData.password_confirmation = userFormData.value.password_confirmation;
    }
  }

  console.log('Submitting user data:', updatedData);

  // Check if there are any changes besides current_password
  if (Object.keys(updatedData).length === 1) {
    successMessage.value = 'No changes detected to update.';
    return;
  }

  try {
    const response = await api.patch('/user', updatedData);
    successMessage.value = response.data.message || 'Profile updated successfully!';

    // Update original data with new values
    if (updatedData.name) originalUserData.value.name = updatedData.name;
    if (updatedData.phone) originalUserData.value.phone = updatedData.phone;

    // Clear password fields
    userFormData.value.password = '';
    userFormData.value.password_confirmation = '';
    userFormData.value.current_password = '';

    // Emit profile updated event
    emit('profileUpdated');

    // Auto-close modal after 2 seconds on success
    setTimeout(() => {
      handleClose();
    }, 2000);

  } catch (error: any) {
    console.error('User profile update error:', error);
    console.error('Error response:', error.response?.data);
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {};
      errorMessage.value = 'Please check the form for validation errors.';
    } else if (error.response?.status === 403) {
      errorMessage.value = error.response.data.error || 'Current password is incorrect.';
    } else if (error.response?.data?.message) {
      successMessage.value = error.response.data.message;
    } else {
      errorMessage.value = `Failed to update profile. ${error.response?.data?.error || error.message || 'Unknown error'}`;
    }
  }
};

// Handle NGO profile submission (from NgoProfileUpdate.vue)
const handleNgoSubmit = async () => {
  errorMessage.value = '';
  validationErrors.value = {};
  successMessage.value = '';

  // Require current password for any update
  if (!ngoFormData.value.current_password) {
    errorMessage.value = 'Current password is required to update the NGO profile.';
    return;
  }

  // Prepare data to send: include current password and changed fields
  const updatedData: Partial<NgoFormData> = {
    current_password: ngoFormData.value.current_password
  };

  if (ngoFormData.value.name !== originalNgoData.value.name) {
    updatedData.name = ngoFormData.value.name;
  }
  if (ngoFormData.value.description !== originalNgoData.value.description) {
    updatedData.description = ngoFormData.value.description;
  }
  if (ngoFormData.value.phone !== originalNgoData.value.phone) {
    updatedData.phone = ngoFormData.value.phone;
  }
  if (ngoFormData.value.based_in !== originalNgoData.value.based_in) {
    updatedData.based_in = ngoFormData.value.based_in;
  }
  if (ngoFormData.value.website !== originalNgoData.value.website) {
    updatedData.website = ngoFormData.value.website;
  }
  if (ngoFormData.value.director_name !== originalNgoData.value.director_name) {
    updatedData.director_name = ngoFormData.value.director_name;
  }
  if (ngoFormData.value.director_phone !== originalNgoData.value.director_phone) {
    updatedData.director_phone = ngoFormData.value.director_phone;
  }

  console.log('Submitting NGO data:', updatedData);
  console.log('NGO ID:', props.ngo?.id);

  // Check if there are any changes besides current_password
  if (Object.keys(updatedData).length === 1) {
    successMessage.value = 'No changes detected to update.';
    return;
  }

  try {
    const response = await api.patch(`/ngo/${props.ngo!.id}`, updatedData);
    successMessage.value = response.data.message || 'NGO profile updated successfully!';

    // Update original data with new values
    Object.keys(updatedData).forEach(key => {
      if (key !== 'current_password' && updatedData[key as keyof NgoFormData]) {
        (originalNgoData.value as any)[key] = updatedData[key as keyof NgoFormData];
      }
    });

    ngoFormData.value.current_password = '';

    // Emit profile updated event
    emit('profileUpdated');

    // Auto-close modal after 2 seconds on success
    setTimeout(() => {
      handleClose();
    }, 2000);

  } catch (error: any) {
    console.error('NGO profile update error:', error);
    console.error('Error response:', error.response?.data);
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {};
      errorMessage.value = 'Please check the form for validation errors.';
    } else if (error.response?.status === 403) {
      errorMessage.value = error.response.data.error || 'Current password is incorrect.';
    } else if (error.response?.data?.message) {
      successMessage.value = error.response.data.message;
    } else {
      errorMessage.value = `Failed to update NGO profile. ${error.response?.data?.error || error.message || 'Unknown error'}`;
    }
  }
};

// Handle form submission
const handleSubmit = () => {
  if (props.isNgoEdit) {
    handleNgoSubmit();
  } else {
    handleUserSubmit();
  }
};

const handleClose = () => {
  emit('close');
};

// Helper methods for form field access
const getUserFieldValue = (field: string) => {
  return userFormData.value[field as keyof UserFormData];
};

const setUserFieldValue = (field: string, value: string) => {
  userFormData.value[field as keyof UserFormData] = value;
};

const getNgoFieldValue = (field: string) => {
  return ngoFormData.value[field as keyof NgoFormData];
};

const setNgoFieldValue = (field: string, value: string) => {
  ngoFormData.value[field as keyof NgoFormData] = value;
};
</script>

<template>
  <!-- Modal Backdrop -->
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="handleClose">
    <!-- Modal Content -->
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" @click.stop>
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-2xl font-bold">
          {{ isNgoEdit ? 'Update NGO Profile' : 'Update Your Profile' }}
        </h2>
        <button @click="handleClose" class="text-gray-400 hover:text-gray-600 text-xl">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="px-6 py-4">
        <!-- Messages -->
        <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-green-800">{{ successMessage }}</p>
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-red-800">{{ errorMessage }}</p>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- User Profile Form -->
          <template v-if="!isNgoEdit">
            <div v-for="field in userFields" :key="field.id">
              <label :for="field.id" class="block text-sm font-medium text-gray-700">{{ field.label }}</label>
              <input
                :id="field.id"
                :type="field.type"
                :value="getUserFieldValue(field.model)"
                @input="setUserFieldValue(field.model, $event.target.value)"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
                :placeholder="field.placeholder"
              />
              <div v-if="validationErrors[field.model]" class="text-red-600 text-sm mt-1">
                {{ validationErrors[field.model][0] }}
              </div>
            </div>
          </template>

          <!-- NGO Profile Form -->
          <template v-else>
            <!-- Full width fields -->
            <div v-for="field in ngoFieldsFullWidth" :key="field.id">
              <label :for="field.id" class="block text-sm font-medium text-gray-700">{{ field.label }}</label>
              <textarea
                v-if="field.type === 'textarea'"
                :id="field.id"
                :value="getNgoFieldValue(field.model)"
                @input="setNgoFieldValue(field.model, $event.target.value)"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
                rows="3"
                :placeholder="field.placeholder"
              ></textarea>
              <input
                v-else
                :id="field.id"
                :type="field.type"
                :value="getNgoFieldValue(field.model)"
                @input="setNgoFieldValue(field.model, $event.target.value)"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
                :placeholder="field.placeholder"
              />
              <div v-if="validationErrors[field.model]" class="text-red-600 text-sm mt-1">
                {{ validationErrors[field.model][0] }}
              </div>
            </div>

            <!-- Two column grid for remaining fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="field in ngoFieldsGrid" :key="field.id">
                <label :for="field.id" class="block text-sm font-medium text-gray-700">{{ field.label }}</label>
                <input
                  :id="field.id"
                  :type="field.type"
                  :value="getNgoFieldValue(field.model)"
                  @input="setNgoFieldValue(field.model, $event.target.value)"
                  class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
                  :placeholder="field.placeholder"
                />
                <div v-if="validationErrors[field.model]" class="text-red-600 text-sm mt-1">
                  {{ validationErrors[field.model][0] }}
                </div>
              </div>
            </div>
          </template>

          <!-- Current Password (always required) -->
          <div>
            <label :for="isNgoEdit ? 'ngo_current_password' : 'current_password'" class="block text-sm font-medium text-gray-700">
              Current Password <span class="text-red-500">*</span>
            </label>
            <input
              :id="isNgoEdit ? 'ngo_current_password' : 'current_password'"
              type="password"
              :value="isNgoEdit ? ngoFormData.current_password : userFormData.current_password"
              @input="isNgoEdit ? ngoFormData.current_password = $event.target.value : userFormData.current_password = $event.target.value"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="Enter your current password to confirm changes"
              required
            />
            <div v-if="validationErrors.current_password" class="text-red-600 text-sm mt-1">
              {{ validationErrors.current_password[0] }}
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="flex justify-end space-x-3 pt-4">
            <TertiaryButton @click="handleClose">Cancel</TertiaryButton>
            <button
              type="submit"
              :disabled="!isUpdateEnabled"
              class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isNgoEdit ? 'Update NGO Profile' : 'Update Profile' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
