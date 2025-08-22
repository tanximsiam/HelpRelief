<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { api } from "@/lib/api";

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
  description?: string;
  website?: string;
  established_date?: string;
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
}

// NGO form data interface (from NgoProfileUpdate.vue)
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
const userFormData = ref<UserFormData>({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
});

const ngoFormData = ref<NgoFormData>({
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

const originalUserData = ref<UserFormData>({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
});

const originalNgoData = ref<NgoFormData>({
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

const userRole = ref<string>('');
const successMessage = ref<string>('');
const errorMessage = ref<string>('');
const validationErrors = ref<Record<string, string[]>>({});

// Initialize data on mount and when props change
const initializeData = async () => {
  try {
    if (props.isNgoEdit && props.ngo?.id) {
      // Fetch NGO data
      const response = await api.get(`/ngo/${props.ngo.id}`);
      const ngoData = response.data;

      ngoFormData.value = {
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
      originalNgoData.value = { ...ngoFormData.value };
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
      };
      originalUserData.value = { ...userFormData.value };
      userRole.value = userData.role || '';
    }
  } catch (error) {
    errorMessage.value = 'Failed to load profile data.';
  }

  // Reset messages
  successMessage.value = '';
  validationErrors.value = {};
};

onMounted(initializeData);
watch([() => props.isNgoEdit, () => props.ngo?.id], initializeData);

// Handle user profile submission (from UserProfileUpdate.vue)
const handleUserSubmit = async () => {
  errorMessage.value = '';
  validationErrors.value = {};
  successMessage.value = '';

  // Prepare data to send: only include fields that differ from original or are non-empty
  const updatedData: Partial<UserFormData> = {};

  if (userFormData.value.name && userFormData.value.name !== originalUserData.value.name) {
    updatedData.name = userFormData.value.name;
  }
  if (userRole.value === 'general' && userFormData.value.email && userFormData.value.email !== originalUserData.value.email) {
    updatedData.email = userFormData.value.email;
  }
  if (userFormData.value.phone && userFormData.value.phone !== originalUserData.value.phone) {
    updatedData.phone = userFormData.value.phone;
  }
  if (userFormData.value.password) {
    updatedData.password = userFormData.value.password;
    if (userFormData.value.password_confirmation) {
      updatedData.password_confirmation = userFormData.value.password_confirmation;
    }
  }

  if (Object.keys(updatedData).length === 0) {
    successMessage.value = 'No changes provided.';
    return;
  }

  try {
    const response = await api.patch('/user', updatedData);
    successMessage.value = response.data.message || 'Profile updated successfully!';

    // Update original data with new values
    originalUserData.value = { ...originalUserData.value, ...updatedData };
    userFormData.value.password = '';
    userFormData.value.password_confirmation = '';

    // Emit profile updated event
    emit('profileUpdated');

  } catch (error: any) {
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {};
    } else if (error.response?.data?.message) {
      successMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Failed to update profile.';
    }
  }
};

// Handle NGO profile submission (from NgoProfileUpdate.vue)
const handleNgoSubmit = async () => {
  errorMessage.value = '';
  validationErrors.value = {};
  successMessage.value = '';

  // Prepare data to send: only include fields that differ from original or are non-empty
  const updatedData: Partial<NgoFormData> = {};

  if (ngoFormData.value.name && ngoFormData.value.name !== originalNgoData.value.name) {
    updatedData.name = ngoFormData.value.name;
  }
  if (ngoFormData.value.description && ngoFormData.value.description !== originalNgoData.value.description) {
    updatedData.description = ngoFormData.value.description;
  }
  if (ngoFormData.value.phone && ngoFormData.value.phone !== originalNgoData.value.phone) {
    updatedData.phone = ngoFormData.value.phone;
  }
  if (ngoFormData.value.based_in && ngoFormData.value.based_in !== originalNgoData.value.based_in) {
    updatedData.based_in = ngoFormData.value.based_in;
  }
  if (ngoFormData.value.cause_focus && ngoFormData.value.cause_focus !== originalNgoData.value.cause_focus) {
    updatedData.cause_focus = ngoFormData.value.cause_focus;
  }
  if (ngoFormData.value.website && ngoFormData.value.website !== originalNgoData.value.website) {
    updatedData.website = ngoFormData.value.website;
  }
  if (ngoFormData.value.registration_no && ngoFormData.value.registration_no !== originalNgoData.value.registration_no) {
    updatedData.registration_no = ngoFormData.value.registration_no;
  }
  if (ngoFormData.value.established_year && ngoFormData.value.established_year !== originalNgoData.value.established_year) {
    updatedData.established_year = ngoFormData.value.established_year;
  }
  if (ngoFormData.value.director_name && ngoFormData.value.director_name !== originalNgoData.value.director_name) {
    updatedData.director_name = ngoFormData.value.director_name;
  }
  if (ngoFormData.value.director_phone && ngoFormData.value.director_phone !== originalNgoData.value.director_phone) {
    updatedData.director_phone = ngoFormData.value.director_phone;
  }
  if (ngoFormData.value.num_employees && ngoFormData.value.num_employees !== originalNgoData.value.num_employees) {
    updatedData.num_employees = ngoFormData.value.num_employees;
  }
  if (ngoFormData.value.logo_url && ngoFormData.value.logo_url !== originalNgoData.value.logo_url) {
    updatedData.logo_url = ngoFormData.value.logo_url;
  }

  if (Object.keys(updatedData).length === 0) {
    successMessage.value = 'No changes provided.';
    return;
  }

  try {
    const response = await api.patch(`/ngo/${props.ngo!.id}`, updatedData);
    successMessage.value = response.data.message || 'NGO profile updated successfully!';

    // Update original data with new values
    originalNgoData.value = { ...originalNgoData.value, ...updatedData };

    // Emit profile updated event
    emit('profileUpdated');

  } catch (error: any) {
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {};
    } else if (error.response?.status === 403) {
      errorMessage.value = error.response.data.error || 'Unauthorized.';
    } else if (error.response?.data?.message) {
      successMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Failed to update NGO profile.';
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
</script>

<template>
  <!-- Modal Backdrop -->
  <div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click="handleClose"
  >
    <!-- Modal Content -->
    <div
      class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-2xl font-bold">
          {{ isNgoEdit ? 'Update NGO Profile' : 'Update Your Profile' }}
        </h2>
        <button
          @click="handleClose"
          class="text-gray-400 hover:text-gray-600 text-xl"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="px-6 py-4">
        <!-- Success/Error Messages -->
        <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
          {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
          {{ errorMessage }}
        </div>

        <!-- User Profile Form -->
        <form v-if="!isNgoEdit" @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input
              id="name"
              v-model="userFormData.name"
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
              v-model="userFormData.email"
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
              v-model="userFormData.phone"
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
              v-model="userFormData.password"
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
              v-model="userFormData.password_confirmation"
              type="password"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="handleClose"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              Update Profile
            </button>
          </div>
        </form>

        <!-- NGO Profile Form -->
        <form v-else @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Name -->
          <div>
            <label for="ngo_name" class="block text-sm font-medium text-gray-700">Name</label>
            <input
              id="ngo_name"
              v-model="ngoFormData.name"
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
              v-model="ngoFormData.description"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
              rows="3"
              placeholder="Leave blank to keep current"
            ></textarea>
            <div v-if="validationErrors.description" class="text-red-600 text-sm mt-1">
              {{ validationErrors.description[0] }}
            </div>
          </div>

          <!-- Two column grid for remaining fields -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Phone -->
            <div>
              <label for="ngo_phone" class="block text-sm font-medium text-gray-700">Phone</label>
              <input
                id="ngo_phone"
                v-model="ngoFormData.phone"
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
                v-model="ngoFormData.based_in"
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
                v-model="ngoFormData.cause_focus"
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
                v-model="ngoFormData.website"
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
                v-model="ngoFormData.registration_no"
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
                v-model="ngoFormData.established_year"
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
                v-model="ngoFormData.director_name"
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
                v-model="ngoFormData.director_phone"
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
                v-model="ngoFormData.num_employees"
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
                v-model="ngoFormData.logo_url"
                type="url"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Leave blank to keep current"
              />
              <div v-if="validationErrors.logo_url" class="text-red-600 text-sm mt-1">
                {{ validationErrors.logo_url[0] }}
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="handleClose"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              Update NGO Profile
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
