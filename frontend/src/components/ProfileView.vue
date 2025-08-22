<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '@/stores/auth';
import { api } from '@/lib/api';
import ProfileEditModal from '@/components/ProfileEditModal.vue';

const auth = useAuth();

// Profile state
const isNgoStaff = ref(false);
const userProfileData = ref<{
  id?: number;
  name?: string;
  email?: string;
  phone?: string;
  role?: string;
  volunteer?: boolean;
  ngo_id?: number | null;
} | null>(null);
const ngoData = ref<{
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
} | null>(null);
const showNgoProfile = ref(false);
const loading = ref(true);
const error = ref<string>('');

// Profile edit modal state
const isEditModalOpen = ref(false);

// Fetch user and NGO data when component mounts
onMounted(async () => {
  try {
    // Ensure user data is loaded
    if (auth.token && !auth.user) {
      await auth.fetchUser();
    }

    // Fetch complete user profile data
    try {
      const userRes = await api.get('/user');
      userProfileData.value = userRes.data;
      console.log('User profile data:', userRes.data);
    } catch (userError) {
      console.error('Error fetching user profile:', userError);
      error.value = 'Failed to load user profile data';
      return;
    }

    // Check if user is NGO staff and get NGO details
    try {
      const staffRes = await api.get('/ngo-staff');
      const staffData = staffRes.data;
      if (staffData.role === 'ngo_staff' && staffData.ngo_id) {
        isNgoStaff.value = true;

        // Check if NGO data is already included in the staff response
        if (staffData.ngo) {
          ngoData.value = staffData.ngo;
        } else {
          // Fallback: Fetch NGO details separately
          try {
            const ngoRes = await api.get(`/ngos/${staffData.ngo_id}`);
            ngoData.value = ngoRes.data;
          } catch (ngoError) {
            console.error('Error fetching NGO details:', ngoError);
            // Use basic NGO info from staff data
            ngoData.value = {
              id: staffData.ngo_id,
              name: 'NGO Information',
              email: 'Contact NGO for details'
            };
          }
        }
      }
    } catch (error) {
      console.log('User is not NGO staff or error fetching NGO data:', error);
    }
  } catch (err) {
    error.value = 'Failed to load profile data';
    console.error('Profile loading error:', err);
  } finally {
    loading.value = false;
  }
});

const displayName = computed(() => {
  return userProfileData.value?.name || auth.user?.name || 'User';
});

const displayEmail = computed(() => {
  return userProfileData.value?.email || auth.user?.email || 'No email';
});

const displayPhone = computed(() => {
  return userProfileData.value?.phone || 'Not provided';
});

const displayRole = computed(() => {
  return userProfileData.value?.role || auth.user?.role || 'general';
});

// Computed properties for modal data
const currentUserProfile = computed(() => {
  if (userProfileData.value) {
    return {
      id: userProfileData.value.id || 0,
      name: userProfileData.value.name || '',
      email: userProfileData.value.email || '',
      role: userProfileData.value.role || 'general',
      volunteer: userProfileData.value.volunteer,
      ngo_id: userProfileData.value.ngo_id
    };
  }
  return auth.user;
});
const currentNgoProfile = computed(() => ngoData.value);

function toggleProfileView() {
  if (isNgoStaff.value) {
    showNgoProfile.value = !showNgoProfile.value;
  }
}

function openEditModal() {
  isEditModalOpen.value = true;
}

function closeEditModal() {
  isEditModalOpen.value = false;
}

function handleProfileUpdate() {
  // Refresh user data after profile update
  if (auth.token) {
    auth.fetchUser();
    // Also refresh the local user profile data
    api.get('/user').then(response => {
      userProfileData.value = response.data;
    }).catch(error => {
      console.error('Error refreshing user profile:', error);
    });
  }
  closeEditModal();
}

function formatDate(dateString?: string) {
  if (!dateString) return 'Not available';
  return new Date(dateString).toLocaleDateString();
}
</script>

<template>
  <div class="bg-white p-6 rounded-lg shadow-lg h-full flex flex-col overflow-hidden">
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <h3 class="text-xl font-semibold text-gray-800">Profile</h3>

      <div class="flex items-center gap-2">
        <!-- Edit Profile Button -->
        <button
          @click="openEditModal"
          class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors"
          title="Edit Profile"
        >
          <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
          Edit
        </button>

        <!-- Toggle button for NGO staff -->
        <button
          v-if="isNgoStaff"
          @click="toggleProfileView"
          class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-colors"
        >
          {{ showNgoProfile ? 'User Profile' : 'NGO Profile' }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex-1 flex items-center justify-center">
      <div class="text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Loading profile...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex-1 flex items-center justify-center">
      <p class="text-red-600">{{ error }}</p>
    </div>

    <!-- User Profile -->
    <div v-else-if="!showNgoProfile" class="flex-1 flex flex-col min-h-0">
      <div class="flex items-center space-x-4 mb-6 flex-shrink-0">
        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
          <span class="text-white text-2xl font-semibold">
            {{ displayName.charAt(0).toUpperCase() }}
          </span>
        </div>
        <div>
          <h4 class="text-xl font-medium text-gray-900">{{ displayName }}</h4>
          <p class="text-gray-600">{{ displayEmail }}</p>
          <span v-if="isNgoStaff" class="inline-block px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full mt-1">
            NGO Staff
          </span>
        </div>
      </div>

      <div class="border-t pt-6 flex-1 overflow-y-auto min-h-0">
        <div class="grid grid-cols-1 gap-4">
          <div class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Email</span>
            <p class="text-gray-900 text-lg mt-1">{{ displayEmail }}</p>
          </div>
          <div class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Phone</span>
            <p class="text-gray-900 text-lg mt-1">{{ displayPhone }}</p>
          </div>
          <div class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Account Type</span>
            <p class="text-gray-900 text-lg mt-1">{{ isNgoStaff ? 'NGO Staff Member' : (displayRole === 'admin' ? 'Administrator' : 'General User') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- NGO Profile -->
    <div v-else-if="showNgoProfile && ngoData" class="flex-1 flex flex-col min-h-0">
      <div class="flex items-center space-x-4 mb-6 flex-shrink-0">
        <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-blue-600 rounded-full flex items-center justify-center">
          <span class="text-white text-2xl font-semibold">
            {{ ngoData.name?.charAt(0).toUpperCase() || 'N' }}
          </span>
        </div>
        <div>
          <h4 class="text-xl font-medium text-gray-900">{{ ngoData.name || 'NGO Name' }}</h4>
          <p class="text-gray-600">{{ ngoData.email || 'No email' }}</p>
          <span class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full mt-1">
            NGO Organization
          </span>
        </div>
      </div>

      <div class="border-t pt-6 flex-1 overflow-y-auto min-h-0">
        <div class="grid grid-cols-1 gap-4">
          <div class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Organization Name</span>
            <p class="text-gray-900 text-lg mt-1">{{ ngoData.name || 'Not provided' }}</p>
          </div>

          <div class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Email</span>
            <p class="text-gray-900 text-lg mt-1">{{ ngoData.email || 'Not provided' }}</p>
          </div>

          <div v-if="ngoData.phone" class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Phone</span>
            <p class="text-gray-900 text-lg mt-1">{{ ngoData.phone }}</p>
          </div>

          <div v-if="ngoData.address" class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Address</span>
            <p class="text-gray-900 text-lg mt-1">{{ ngoData.address }}</p>
          </div>

          <div v-if="ngoData.type" class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">NGO Type</span>
            <p class="text-gray-900 text-lg mt-1">{{ ngoData.type }}</p>
          </div>

          <div v-if="ngoData.registration_number" class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Registration Number</span>
            <p class="text-gray-900 text-lg mt-1">{{ ngoData.registration_number }}</p>
          </div>

          <div v-if="ngoData.description" class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Description</span>
            <p class="text-gray-900 text-lg mt-1">{{ ngoData.description }}</p>
          </div>

          <div v-if="ngoData.website" class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Website</span>
            <a :href="ngoData.website" target="_blank" class="text-blue-600 hover:text-blue-800 text-lg mt-1 block underline">
              {{ ngoData.website }}
            </a>
          </div>

          <div v-if="ngoData.established_date" class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Established Date</span>
            <p class="text-gray-900 text-lg mt-1">{{ formatDate(ngoData.established_date) }}</p>
          </div>

          <div class="p-4 bg-green-50 rounded-lg border border-green-200">
            <span class="text-sm font-medium text-green-600">Organization Status</span>
            <p class="text-green-700 font-medium text-lg mt-1">Active & Verified</p>
          </div>
        </div>
      </div>
    </div>

    <!-- No NGO Data State -->
    <div v-else-if="showNgoProfile && !ngoData" class="flex-1 flex items-center justify-center">
      <div class="text-center">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 14c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <p class="text-gray-600 font-medium">NGO Profile Not Available</p>
        <p class="text-gray-500 text-sm mt-1">Contact your NGO administrator for assistance</p>
      </div>
    </div>

    <!-- Profile Edit Modal -->
    <ProfileEditModal
      v-if="isEditModalOpen"
      :user="currentUserProfile"
      :ngo="currentNgoProfile"
      :is-ngo-edit="showNgoProfile"
      @close="closeEditModal"
      @profile-updated="handleProfileUpdate"
    />
  </div>
</template>
