<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { api } from "@/lib/api";

// Define form data interface
interface UserFormData {
  name: string;
  email: string;
  phone: string;
  password: string;
  password_confirmation: string;
}

// State
const formData = ref<UserFormData>({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
});
const originalData = ref<UserFormData>({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
});
const userRole = ref<string>('');
const successMessage = ref<string>('');
const errorMessage = ref<string>('');
const validationErrors = ref<Record<string, string[]>>({});

// Fetch user data on mount
onMounted(async () => {
  try {
    const response = await api.get('/user');
    const userData = response.data;
    formData.value = {
      name: userData.name || '',
      email: userData.email || '',
      phone: userData.phone || '',
      password: '',
      password_confirmation: '',
    };
    originalData.value = { ...formData.value }; // Store original data
    userRole.value = userData.role || '';
  } catch (error) {
    errorMessage.value = 'Failed to load user data.';
  }
});

// Handle form submission with only changed/non-empty fields
const handleSubmit = async () => {
  errorMessage.value = '';
  validationErrors.value = {};
  successMessage.value = '';

  // Prepare data to send: only include fields that differ from original or are non-empty
  const updatedData: Partial<UserFormData> = {};
  if (formData.value.name && formData.value.name !== originalData.value.name) {
    updatedData.name = formData.value.name;
  }
  if (userRole.value === 'general' && formData.value.email && formData.value.email !== originalData.value.email) {
    updatedData.email = formData.value.email;
  }
  if (formData.value.phone && formData.value.phone !== originalData.value.phone) {
    updatedData.phone = formData.value.phone;
  }
  if (formData.value.password) {
    updatedData.password = formData.value.password;
    if (formData.value.password_confirmation) {
      updatedData.password_confirmation = formData.value.password_confirmation;
    }
  }

  try {
    const response = await api.patch('/user', updatedData); // Match backend route
    successMessage.value = response.data.message || 'Profile updated successfully!';
    // Update original data with new values
    originalData.value = { ...originalData.value, ...updatedData };
    formData.value.password = '';
    formData.value.password_confirmation = '';
  } catch (error: any) {
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {};
    } else if (error.response?.data?.message) {
      successMessage.value = error.response.data.message; // For "No changes detected"
    } else {
      errorMessage.value = 'Failed to update profile.';
    }
  }
};
</script>

<template>
  <div class="container mx-auto mt-10 max-w-lg">
    <h2 class="text-2xl font-bold mb-6">Update Your Profile</h2>

    <!-- Success/Error Messages -->
    <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Name -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input
          id="name"
          v-model="formData.name"
          type="text"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.name" class="text-red-600 text-sm mt-1">
          {{ validationErrors.name[0] }}
        </div>
      </div>

      <!-- Email (only for general role) -->
      <div v-if="userRole === 'general'">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input
          id="email"
          v-model="formData.email"
          type="email"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.email" class="text-red-600 text-sm mt-1">
          {{ validationErrors.email[0] }}
        </div>
      </div>

      <!-- Phone -->
      <div>
        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
        <input
          id="phone"
          v-model="formData.phone"
          type="text"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.phone" class="text-red-600 text-sm mt-1">
          {{ validationErrors.phone[0] }}
        </div>
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password (leave blank if no change)</label>
        <input
          id="password"
          v-model="formData.password"
          type="password"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
        />
        <div v-if="validationErrors.password" class="text-red-600 text-sm mt-1">
          {{ validationErrors.password[0] }}
        </div>
      </div>

      <!-- Password Confirmation -->
      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
        <input
          id="password_confirmation"
          v-model="formData.password_confirmation"
          type="password"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
        />
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Update Profile
      </button>
    </form>
  </div>
</template>
