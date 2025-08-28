<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '@/stores/auth';
import { api } from '@/lib/api';
import ProfileEditModal from '@/components/ProfileEditModal.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import ToggleProfileButton from '@/components/ToggleProfileButton.vue';

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
  based_in?: string;
  registration_no?: string;
  established_year?: number;
  director_name?: string;
  director_phone?: string;
  num_employees?: number;
  current_employee_count?: number;
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

    // If user is NGO staff, also refresh NGO data
    if (isNgoStaff.value) {
      api.get('/ngo-staff').then(response => {
        const staffData = response.data;
        if (staffData.ngo) {
          ngoData.value = staffData.ngo;
          console.log('NGO data refreshed:', staffData.ngo);
        }
      }).catch(error => {
        console.error('Error refreshing NGO data:', error);
      });
    }
  }
  closeEditModal();
}

function formatDate(dateString?: string) {
  if (!dateString) return 'Not available';
  // If it's just a year (4 digits), return it as is
  if (/^\d{4}$/.test(dateString)) return dateString;
  return new Date(dateString).toLocaleDateString();
}
</script>

<template>
  <!-- Loading State -->
  <div v-if="loading" class="bg-white rounded-lg shadow-lg p-8">
    <div class="flex items-center justify-center min-h-[400px]">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-4 text-lg">Loading profile...</p>
      </div>
    </div>
  </div>

  <!-- Error State -->
  <div v-else-if="error" class="bg-white rounded-lg shadow-lg p-8">
    <div class="flex items-center justify-center min-h-[400px]">
      <div class="text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 14c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <p class="text-red-600 text-lg font-medium">{{ error }}</p>
      </div>
    </div>
  </div>

  <!-- Main Profile Content -->
  <div v-else class="bg-white rounded-lg shadow-lg overflow-hidden">
    <!-- Profile Header with Toggle -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-8 py-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <h2 class="text-2xl font-bold text-white">
            {{ showNgoProfile ? 'Organization Profile' : 'Personal Profile' }}
          </h2>
          <span v-if="isNgoStaff && !showNgoProfile" class="inline-block px-3 py-1 text-sm bg-green-100 text-green-800 rounded-full">
            NGO Staff
          </span>
          <span v-else-if="showNgoProfile" class="inline-block px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">
            Organization
          </span>
          <span v-else-if="!showNgoProfile && !isNgoStaff && userProfileData?.volunteer" class="inline-block px-3 py-1 text-sm bg-purple-100 text-purple-800 rounded-full">
            Volunteer
          </span>
        </div>

        <!-- Toggle button for NGO staff -->
        <ToggleProfileButton
          v-if="isNgoStaff"
          @click="toggleProfileView"
        >
          <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
          </svg>
          {{ showNgoProfile ? 'View Personal Profile' : 'View Organization Profile' }}
        </ToggleProfileButton>
      </div>
    </div>

    <!-- User Profile Layout -->
    <div v-if="!showNgoProfile" class="p-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Profile Image -->
        <div class="lg:col-span-1">
          <div class="flex flex-col items-center">
            <!-- Profile Image Placeholder -->
            <img
              src="https://placehold.co/300x300"
              alt="Profile Picture"
              class="w-80 h-80 rounded-lg shadow-lg object-cover"
            />
          </div>
        </div>

        <!-- Right Column - Profile Information -->
        <div class="lg:col-span-2">
          <div class="space-y-6">
            <!-- Basic Information -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Full Name</label>
                  <p class="text-lg text-gray-900 font-medium">{{ displayName }}</p>
                </div>
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Email Address</label>
                  <p class="text-lg text-gray-900">{{ displayEmail }}</p>
                </div>
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Phone Number</label>
                  <p class="text-lg text-gray-900">{{ displayPhone }}</p>
                </div>
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Account Type</label>
                  <p class="text-lg text-gray-900">
                    {{ isNgoStaff ? 'NGO Staff Member' : (displayRole === 'admin' ? 'Administrator' : 'General User') }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Account Details -->
            <div class="border-t pt-6">
              <h3 class="text-xl font-semibold text-gray-900 mb-4">Account Details</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">User ID</label>
                  <p class="text-lg text-gray-900">#{{ userProfileData?.id || 'N/A' }}</p>
                </div>
                <div v-if="userProfileData?.volunteer" class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Volunteer Status</label>
                  <span class="inline-block px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">
                    Registered Volunteer
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="border-t pt-6 flex justify-end">
              <SecondaryButton
                @click="openEditModal"
                class="px-6 py-3"
              >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Update Profile
              </SecondaryButton>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- NGO Profile Layout -->
    <div v-else-if="showNgoProfile && ngoData" class="p-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Organization Logo -->
        <div class="lg:col-span-1">
          <div class="flex flex-col items-center">
            <!-- Organization Logo Placeholder -->
            <img
              src="https://placehold.co/300x300"
              alt="Organization Logo"
              class="w-80 h-80 rounded-lg shadow-lg object-cover"
            />
          </div>
        </div>

        <!-- Right Column - Organization Information -->
        <div class="lg:col-span-2">
          <div class="space-y-6">
            <!-- Organization Details -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4">Organization Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Organization ID</label>
                  <p class="text-lg text-gray-900 font-medium">#{{ ngoData.id || 'N/A' }}</p>
                </div>
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Organization Name</label>
                  <p class="text-lg text-gray-900 font-medium">{{ ngoData.name || 'Not provided' }}</p>
                </div>
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Email Address</label>
                  <p class="text-lg text-gray-900">{{ ngoData.email || 'Not provided' }}</p>
                </div>
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Phone Number</label>
                  <p class="text-lg text-gray-900">{{ ngoData.phone || 'Not provided' }}</p>
                </div>
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Based In</label>
                  <p class="text-lg text-gray-900">{{ ngoData.based_in || 'Not provided' }}</p>
                </div>
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Website</label>
                  <p v-if="ngoData.website">
                    <a :href="ngoData.website" target="_blank" class="text-lg text-blue-600 hover:text-blue-800 underline">
                      {{ ngoData.website }}
                    </a>
                  </p>
                  <p v-else class="text-lg text-gray-900">Not provided</p>
                </div>
              </div>
            </div>

            <!-- Registration & Legal Details -->
            <div class="border-t pt-6">
              <h3 class="text-xl font-semibold text-gray-900 mb-4">Registration & Legal Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Registration Number</label>
                  <p class="text-lg text-gray-900">{{ ngoData.registration_no || ngoData.registration_number || 'Not provided' }}</p>
                </div>
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Established Year</label>
                  <p class="text-lg text-gray-900">{{ ngoData.established_year || formatDate(ngoData.established_date) || 'Not provided' }}</p>
                </div>
              </div>
            </div>

            <!-- Leadership & Staff Information -->
            <div class="border-t pt-6">
              <h3 class="text-xl font-semibold text-gray-900 mb-4">Leadership & Staff</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Director Name</label>
                  <p class="text-lg text-gray-900">{{ ngoData.director_name || 'Not provided' }}</p>
                </div>
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Director Phone</label>
                  <p class="text-lg text-gray-900">{{ ngoData.director_phone || 'Not provided' }}</p>
                </div>
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-500">Active Employees</label>
                  <p class="text-lg text-gray-900">{{ ngoData.current_employee_count || 0 }}</p>
                </div>
              </div>
            </div>

            <!-- Description -->
            <div v-if="ngoData.description" class="border-t pt-6">
              <h3 class="text-xl font-semibold text-gray-900 mb-4">About</h3>
              <p class="text-lg text-gray-700 leading-relaxed">{{ ngoData.description }}</p>
            </div>

            <!-- Action Buttons -->
            <div class="border-t pt-6 flex justify-end">
              <SecondaryButton
                @click="openEditModal"
                class="px-6 py-3"
              >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Update Organization
              </SecondaryButton>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No NGO Data State -->
    <div v-else-if="showNgoProfile && !ngoData" class="p-8">
      <div class="flex items-center justify-center min-h-[400px]">
        <div class="text-center">
          <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 14c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">Organization Profile Not Available</h3>
          <p class="text-gray-600">Contact your NGO administrator for assistance</p>
        </div>
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
