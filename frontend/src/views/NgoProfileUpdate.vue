<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { api } from "@/lib/api";

// Define form data interface
interface NgoFormData {
  name: string;
  description: string;
  phone: string;
  based_in: string;
  cause_focus: string;
  website: string;
  registration_no: string;
  established_year: string;
  director_name: string;
  director_phone: string;
  num_employees: string;
  logo_url: string;
}

// State
const route = useRoute();
const ngoId = route.params.ngoId as string || '';
const formData = ref<NgoFormData>({
  name: '',
  description: '',
  phone: '',
  based_in: '',
  cause_focus: '',
  website: '',
  registration_no: '',
  established_year: '',
  director_name: '',
  director_phone: '',
  num_employees: '',
  logo_url: '',
});
const originalData = ref<NgoFormData>({
  name: '',
  description: '',
  phone: '',
  based_in: '',
  cause_focus: '',
  website: '',
  registration_no: '',
  established_year: '',
  director_name: '',
  director_phone: '',
  num_employees: '',
  logo_url: '',
});
const successMessage = ref<string>('');
const errorMessage = ref<string>('');
const validationErrors = ref<Record<string, string[]>>({});

// Fetch NGO data on mount
onMounted(async () => {
  try {
    const response = await api.get(`/ngo/${ngoId}`);
    const ngoData = response.data;
    formData.value = {
      name: ngoData.name || '',
      description: ngoData.description || '',
      phone: ngoData.phone || '',
      based_in: ngoData.based_in || '',
      cause_focus: ngoData.cause_focus || '',
      website: ngoData.website || '',
      registration_no: ngoData.registration_no || '',
      established_year: ngoData.established_year || '',
      director_name: ngoData.director_name || '',
      director_phone: ngoData.director_phone || '',
      num_employees: ngoData.num_employees || '',
      logo_url: ngoData.logo_url || '',
    };
    originalData.value = { ...formData.value }; // Store original data
  } catch (error) {
    errorMessage.value = 'Failed to load NGO data.';
  }
});

// Handle form submission with only changed/non-empty fields
const handleSubmit = async () => {
  errorMessage.value = '';
  validationErrors.value = {};
  successMessage.value = '';

  // Prepare data to send: only include fields that differ from original or are non-empty
  const updatedData: Partial<NgoFormData> = {};
  if (formData.value.name && formData.value.name !== originalData.value.name) {
    updatedData.name = formData.value.name;
  }
  if (formData.value.description && formData.value.description !== originalData.value.description) {
    updatedData.description = formData.value.description;
  }
  if (formData.value.phone && formData.value.phone !== originalData.value.phone) {
    updatedData.phone = formData.value.phone;
  }
  if (formData.value.based_in && formData.value.based_in !== originalData.value.based_in) {
    updatedData.based_in = formData.value.based_in;
  }
  if (formData.value.cause_focus && formData.value.cause_focus !== originalData.value.cause_focus) {
    updatedData.cause_focus = formData.value.cause_focus;
  }
  if (formData.value.website && formData.value.website !== originalData.value.website) {
    updatedData.website = formData.value.website;
  }
  if (formData.value.registration_no && formData.value.registration_no !== originalData.value.registration_no) {
    updatedData.registration_no = formData.value.registration_no;
  }
  if (formData.value.established_year && formData.value.established_year !== originalData.value.established_year) {
    updatedData.established_year = formData.value.established_year;
  }
  if (formData.value.director_name && formData.value.director_name !== originalData.value.director_name) {
    updatedData.director_name = formData.value.director_name;
  }
  if (formData.value.director_phone && formData.value.director_phone !== originalData.value.director_phone) {
    updatedData.director_phone = formData.value.director_phone;
  }
  if (formData.value.num_employees && formData.value.num_employees !== originalData.value.num_employees) {
    updatedData.num_employees = formData.value.num_employees;
  }
  if (formData.value.logo_url && formData.value.logo_url !== originalData.value.logo_url) {
    updatedData.logo_url = formData.value.logo_url;
  }

  try {
    const response = await api.patch(`/ngo/${ngoId}`, updatedData);
    successMessage.value = response.data.message || 'NGO profile updated successfully!';
    originalData.value = { ...originalData.value, ...updatedData };
  } catch (error: any) {
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {};
    } else if (error.response?.status === 403) {
      errorMessage.value = error.response.data.error || 'Unauthorized.';
    } else if (error.response?.data?.message) {
      successMessage.value = error.response.data.message; // For "No changes provided"
    } else {
      errorMessage.value = 'Failed to update NGO profile.';
    }
  }
};
</script>

<template>
  <div class="container mx-auto mt-10 max-w-lg">
    <h2 class="text-2xl font-bold mb-6">Update NGO Profile</h2>

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

      <!-- Description -->
      <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea
          id="description"
          v-model="formData.description"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        ></textarea>
        <div v-if="validationErrors.description" class="text-red-600 text-sm mt-1">
          {{ validationErrors.description[0] }}
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

      <!-- Based In -->
      <div>
        <label for="based_in" class="block text-sm font-medium text-gray-700">Based In (Location)</label>
        <input
          id="based_in"
          v-model="formData.based_in"
          type="text"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.based_in" class="text-red-600 text-sm mt-1">
          {{ validationErrors.based_in[0] }}
        </div>
      </div>

      <!-- Cause Focus -->
      <div>
        <label for="cause_focus" class="block text-sm font-medium text-gray-700">Cause Focus</label>
        <input
          id="cause_focus"
          v-model="formData.cause_focus"
          type="text"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.cause_focus" class="text-red-600 text-sm mt-1">
          {{ validationErrors.cause_focus[0] }}
        </div>
      </div>

      <!-- Website -->
      <div>
        <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
        <input
          id="website"
          v-model="formData.website"
          type="url"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.website" class="text-red-600 text-sm mt-1">
          {{ validationErrors.website[0] }}
        </div>
      </div>

      <!-- Registration No -->
      <div>
        <label for="registration_no" class="block text-sm font-medium text-gray-700">Registration No</label>
        <input
          id="registration_no"
          v-model="formData.registration_no"
          type="text"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.registration_no" class="text-red-600 text-sm mt-1">
          {{ validationErrors.registration_no[0] }}
        </div>
      </div>

      <!-- Established Year -->
      <div>
        <label for="established_year" class="block text-sm font-medium text-gray-700">Established Year</label>
        <input
          id="established_year"
          v-model="formData.established_year"
          type="number"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.established_year" class="text-red-600 text-sm mt-1">
          {{ validationErrors.established_year[0] }}
        </div>
      </div>

      <!-- Director Name -->
      <div>
        <label for="director_name" class="block text-sm font-medium text-gray-700">Director Name</label>
        <input
          id="director_name"
          v-model="formData.director_name"
          type="text"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.director_name" class="text-red-600 text-sm mt-1">
          {{ validationErrors.director_name[0] }}
        </div>
      </div>

      <!-- Director Phone -->
      <div>
        <label for="director_phone" class="block text-sm font-medium text-gray-700">Director Phone</label>
        <input
          id="director_phone"
          v-model="formData.director_phone"
          type="text"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.director_phone" class="text-red-600 text-sm mt-1">
          {{ validationErrors.director_phone[0] }}
        </div>
      </div>

      <!-- Number of Employees -->
      <div>
        <label for="num_employees" class="block text-sm font-medium text-gray-700">Number of Employees</label>
        <input
          id="num_employees"
          v-model="formData.num_employees"
          type="number"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.num_employees" class="text-red-600 text-sm mt-1">
          {{ validationErrors.num_employees[0] }}
        </div>
      </div>

      <!-- Logo URL -->
      <div>
        <label for="logo_url" class="block text-sm font-medium text-gray-700">Logo URL</label>
        <input
          id="logo_url"
          v-model="formData.logo_url"
          type="url"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="Leave blank to keep current"
        />
        <div v-if="validationErrors.logo_url" class="text-red-600 text-sm mt-1">
          {{ validationErrors.logo_url[0] }}
        </div>
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Update NGO Profile
      </button>
    </form>
  </div>
</template>
