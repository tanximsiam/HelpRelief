# ProfileView Component Documentation

## Overview
The ProfileView component is a comprehensive user profile management system that provides role-based profile display and editing capabilities. It supports both regular users and NGO staff members, offering seamless profile switching and editing through an integrated modal system.

## Architecture Overview

```
ProfileView Component
    ├── User Profile Display
    ├── NGO Profile Display (NGO Staff Only)
    ├── Role Detection & Data Fetching
    ├── Profile Toggle (NGO Staff)
    └── ProfileEditModal Integration
            ├── User Profile Editing
            └── NGO Profile Editing
```

## Backend Components

### 1. Database Structure

#### Tables Involved:
- **`users`** - User account information
- **`ngos`** - NGO organization details  
- **`ngo_staff`** - Relationship table linking users to NGOs

#### Key Relationships:
```sql
-- Users table (main user accounts)
users (id, name, email, role, phone, etc.)

-- NGOs table (organization details)
ngos (id, name, email, phone, description, website, etc.)

-- NGO Staff relationship table
ngo_staff (id, user_id, ngo_id, role, etc.)
```

### 2. API Endpoints

#### User Profile Endpoints:
- `GET /user` - Get current user profile
- `PATCH /user` - Update user profile
- `GET /ngo-staff` - Check NGO staff status

#### NGO Profile Endpoints:
- `GET /ngo/{id}` - Get NGO details
- `PATCH /ngo/{id}` - Update NGO profile

### 3. Backend Models & Controllers

#### User Profile API Response:
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "role": "general"
}
```

#### NGO Staff API Response:
```json
{
    "role": "ngo_staff",
    "ngo_id": 1,
    "ngo": {
        "id": 1,
        "name": "Relief Organization",
        "email": "contact@relief.org",
        "phone": "+1234567890",
        "description": "Disaster relief NGO",
        "website": "https://relief.org"
    }
}
```

## Frontend Components

### 1. ProfileView.vue - Main Component

#### Purpose:
Displays user profile information with role-based features and profile editing capabilities.

#### Component Structure:
```vue
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '@/stores/auth';
import { api } from '@/lib/api';
import ProfileEditModal from '@/components/ProfileEditModal.vue';

// State management
const auth = useAuth();
const isNgoStaff = ref(false);
const userProfileData = ref(null);
const ngoData = ref(null);
const showNgoProfile = ref(false);
const isEditModalOpen = ref(false);
</script>
```

#### Key Features:

##### 1. Role Detection & Data Fetching
```javascript
onMounted(async () => {
    try {
        // Ensure user authentication
        if (auth.token && !auth.user) {
            await auth.fetchUser();
        }

        // Fetch complete user profile
        const userRes = await api.get('/user');
        userProfileData.value = userRes.data;

        // Check NGO staff status
        try {
            const staffRes = await api.get('/ngo-staff');
            const staffData = staffRes.data;
            
            if (staffData.role === 'ngo_staff' && staffData.ngo_id) {
                isNgoStaff.value = true;
                
                // Get NGO details
                if (staffData.ngo) {
                    ngoData.value = staffData.ngo;
                } else {
                    // Fallback: fetch separately
                    const ngoRes = await api.get(`/ngos/${staffData.ngo_id}`);
                    ngoData.value = ngoRes.data;
                }
            }
        } catch (error) {
            console.log('User is not NGO staff');
        }
    } catch (err) {
        error.value = 'Failed to load profile data';
    } finally {
        loading.value = false;
    }
});
```

##### 2. Computed Properties for Data Display
```javascript
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
```

##### 3. Profile Toggle Functionality (NGO Staff)
```javascript
function toggleProfileView() {
    if (isNgoStaff.value) {
        showNgoProfile.value = !showNgoProfile.value;
    }
}
```

##### 4. Modal Management
```javascript
function openEditModal() {
    console.log('Opening edit modal, isNgoEdit:', showNgoProfile.value);
    isEditModalOpen.value = true;
}

function closeEditModal() {
    console.log('Closing edit modal');
    isEditModalOpen.value = false;
}

async function handleProfileUpdate() {
    try {
        // Refresh user data
        await auth.fetchUser();
        const response = await api.get('/user');
        userProfileData.value = response.data;
        
        // Refresh NGO data if viewing NGO profile
        if (showNgoProfile.value && isNgoStaff.value && ngoData.value?.id) {
            const ngoResponse = await api.get(`/ngo/${ngoData.value.id}`);
            ngoData.value = ngoResponse.data;
        }
    } catch (error) {
        console.error('Error refreshing profile data:', error);
    }
    closeEditModal();
}
```

#### Template Structure:

##### Header Section:
```vue
<div class="flex items-center justify-between mb-6 flex-shrink-0">
    <h3 class="text-xl font-semibold text-gray-800">Profile</h3>
    
    <div class="flex items-center gap-2">
        <!-- Edit Profile Button -->
        <button @click="openEditModal" class="px-3 py-1 text-sm bg-gray-100...">
            Edit
        </button>
        
        <!-- Toggle for NGO Staff -->
        <button v-if="isNgoStaff" @click="toggleProfileView" class="px-3 py-1...">
            {{ showNgoProfile ? 'User Profile' : 'NGO Profile' }}
        </button>
    </div>
</div>
```

##### User Profile Display:
```vue
<div v-if="!showNgoProfile" class="flex-1 flex flex-col min-h-0">
    <!-- Avatar & Basic Info -->
    <div class="flex items-center space-x-4 mb-6">
        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full">
            <span class="text-white text-2xl font-semibold">
                {{ displayName.charAt(0).toUpperCase() }}
            </span>
        </div>
        <div>
            <h4 class="text-xl font-medium text-gray-900">{{ displayName }}</h4>
            <p class="text-gray-600">{{ displayEmail }}</p>
            <span v-if="isNgoStaff" class="badge">NGO Staff</span>
        </div>
    </div>
    
    <!-- Detailed Information Cards -->
    <div class="grid grid-cols-1 gap-4">
        <div class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Email</span>
            <p class="text-gray-900 text-lg mt-1">{{ displayEmail }}</p>
        </div>
        <!-- More info cards -->
    </div>
</div>
```

##### NGO Profile Display:
```vue
<div v-else-if="showNgoProfile && ngoData" class="flex-1 flex flex-col min-h-0">
    <!-- NGO Header -->
    <div class="flex items-center space-x-4 mb-6">
        <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-blue-600 rounded-full">
            <span class="text-white text-2xl font-semibold">
                {{ ngoData.name?.charAt(0).toUpperCase() || 'N' }}
            </span>
        </div>
        <div>
            <h4 class="text-xl font-medium text-gray-900">{{ ngoData.name }}</h4>
            <p class="text-gray-600">{{ ngoData.email }}</p>
            <span class="badge">NGO Organization</span>
        </div>
    </div>
    
    <!-- NGO Details -->
    <div class="grid grid-cols-1 gap-4">
        <div v-if="ngoData.description" class="p-4 bg-gray-50 rounded-lg">
            <span class="text-sm font-medium text-gray-500">Description</span>
            <p class="text-gray-900 text-lg mt-1">{{ ngoData.description }}</p>
        </div>
        <!-- More NGO info cards -->
    </div>
</div>
```

### 2. ProfileEditModal.vue - Edit Component

#### Purpose:
Provides comprehensive profile editing capabilities for both user and NGO profiles through a modal interface.

#### Component Structure:
```vue
<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { api } from "@/lib/api";

// Props & Emits
const props = defineProps<{
    user?: User | null;
    ngo?: Ngo | null;
    isNgoEdit?: boolean;
}>();

const emit = defineEmits<{
    close: [];
    profileUpdated: [];
}>();
</script>
```

#### Key Features:

##### 1. Form Data Management
```javascript
// User form data interface
interface UserFormData {
    name: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
}

// NGO form data interface
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
```

##### 2. Data Initialization
```javascript
const initializeData = async () => {
    try {
        if (props.isNgoEdit && props.ngo?.id) {
            // Fetch complete NGO data
            const response = await api.get(`/ngo/${props.ngo.id}`);
            const ngoData = response.data;
            
            // Populate form
            ngoFormData.value = {
                name: ngoData.name || '',
                description: ngoData.description || '',
                // ... other fields
            };
            originalNgoData.value = { ...ngoFormData.value };
        } else {
            // Fetch complete user data
            const response = await api.get('/user');
            const userData = response.data;
            
            userFormData.value = {
                name: userData.name || '',
                email: userData.email || '',
                // ... other fields
            };
            originalUserData.value = { ...userFormData.value };
            userRole.value = userData.role || '';
        }
    } catch (error) {
        errorMessage.value = 'Failed to load profile data.';
    }
};
```

##### 3. Change Detection & Submission
```javascript
const handleUserSubmit = async () => {
    // Only include changed fields
    const updatedData: Partial<UserFormData> = {};
    
    if (userFormData.value.name && userFormData.value.name !== originalUserData.value.name) {
        updatedData.name = userFormData.value.name;
    }
    
    // Email editing restriction for NGO staff
    if (userRole.value === 'general' && 
        userFormData.value.email && 
        userFormData.value.email !== originalUserData.value.email) {
        updatedData.email = userFormData.value.email;
    }
    
    if (userFormData.value.phone && userFormData.value.phone !== originalUserData.value.phone) {
        updatedData.phone = userFormData.value.phone;
    }
    
    if (userFormData.value.password) {
        updatedData.password = userFormData.value.password;
        updatedData.password_confirmation = userFormData.value.password_confirmation;
    }

    if (Object.keys(updatedData).length === 0) {
        successMessage.value = 'No changes provided.';
        return;
    }

    try {
        const response = await api.patch('/user', updatedData);
        successMessage.value = response.data.message || 'Profile updated successfully!';
        
        // Update original data
        originalUserData.value = { ...originalUserData.value, ...updatedData };
        
        // Clear password fields
        userFormData.value.password = '';
        userFormData.value.password_confirmation = '';
        
        emit('profileUpdated');
    } catch (error) {
        // Handle validation errors
        if (error.response?.status === 422) {
            validationErrors.value = error.response.data.errors || {};
        }
    }
};
```

##### 4. NGO Profile Submission
```javascript
const handleNgoSubmit = async () => {
    // Similar change detection for NGO fields
    const updatedData: Partial<NgoFormData> = {};
    
    // Check each field for changes
    Object.keys(ngoFormData.value).forEach(key => {
        const formKey = key as keyof typeof ngoFormData.value;
        if (ngoFormData.value[formKey] && 
            ngoFormData.value[formKey] !== originalNgoData.value[formKey]) {
            updatedData[key] = ngoFormData.value[formKey];
        }
    });

    if (Object.keys(updatedData).length === 0) {
        successMessage.value = 'No changes provided.';
        return;
    }

    try {
        const response = await api.patch(`/ngo/${props.ngo!.id}`, updatedData);
        successMessage.value = response.data.message || 'NGO profile updated successfully!';
        
        originalNgoData.value = { ...originalNgoData.value, ...updatedData };
        emit('profileUpdated');
    } catch (error) {
        // Handle errors
    }
};
```

#### Modal Template Structure:

##### Modal Header:
```vue
<div class="px-6 py-4 border-b border-gray-200">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">
            {{ isNgoEdit ? 'Edit NGO Profile' : 'Edit Profile' }}
        </h3>
        <button @click="handleClose">×</button>
    </div>
</div>
```

##### Form Fields (User):
```vue
<form v-if="!isNgoEdit" @submit.prevent="handleSubmit">
    <!-- Name Field -->
    <div>
        <label for="name">Name</label>
        <input id="name" v-model="userFormData.name" type="text" 
               placeholder="Leave blank to keep current" />
        <div v-if="validationErrors.name" class="error">
            {{ validationErrors.name[0] }}
        </div>
    </div>
    
    <!-- Email Field (restricted for NGO staff) -->
    <div v-if="userRole === 'general'">
        <label for="email">Email</label>
        <input id="email" v-model="userFormData.email" type="email" />
    </div>
    
    <!-- Other fields -->
</form>
```

##### Form Fields (NGO):
```vue
<form v-else @submit.prevent="handleSubmit">
    <!-- NGO Name -->
    <div class="md:col-span-2">
        <label for="ngo_name">NGO Name</label>
        <input id="ngo_name" v-model="ngoFormData.name" type="text" />
    </div>
    
    <!-- Description -->
    <div class="md:col-span-2">
        <label for="description">Description</label>
        <textarea id="description" v-model="ngoFormData.description" rows="3"></textarea>
    </div>
    
    <!-- Grid layout for other fields -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Phone, Location, Website, etc. -->
    </div>
</form>
```

## User Workflows

### General User Workflow:
1. **View Profile:** See personal information and account details
2. **Edit Profile:** Click "Edit" to modify name, email, phone, password
3. **Save Changes:** Submit form with validation and success feedback

### NGO Staff Workflow:
1. **View User Profile:** See personal account information
2. **Toggle to NGO Profile:** Switch to view organization details
3. **Edit User Profile:** Modify personal info (email editing restricted)
4. **Edit NGO Profile:** Update organization information
5. **Switch Between Views:** Seamlessly toggle between user and NGO profiles

## Key Design Decisions

### 1. Role-Based Feature Access
**Problem:** Different user types need different profile capabilities.

**Solution:**
- **Detection:** Check NGO staff status via API call
- **Conditional Rendering:** Show/hide features based on role
- **Access Control:** Restrict email editing for NGO staff

```javascript
// Role detection
const staffRes = await api.get('/ngo-staff');
if (staffData.role === 'ngo_staff') {
    isNgoStaff.value = true;
    // Enable NGO features
}

// Conditional email editing
<div v-if="userRole === 'general'">
    <input v-model="userFormData.email" type="email" />
</div>
```

### 2. Profile Toggle for NGO Staff
**Problem:** NGO staff need access to both personal and organization profiles.

**Solution:**
- **State Management:** `showNgoProfile` boolean flag
- **Seamless Switching:** Toggle button in header
- **Context Preservation:** Maintain editing context when switching

```javascript
function toggleProfileView() {
    if (isNgoStaff.value) {
        showNgoProfile.value = !showNgoProfile.value;
    }
}
```

### 3. Change Detection for Efficient Updates
**Problem:** Avoid unnecessary API calls and server load.

**Solution:**
- **Original Data Tracking:** Store initial form state
- **Field Comparison:** Only send changed fields
- **Optimistic Updates:** Update local state immediately

```javascript
// Track original data
const originalUserData = ref({ ...userFormData.value });

// Only send changes
const updatedData = {};
if (userFormData.value.name !== originalUserData.value.name) {
    updatedData.name = userFormData.value.name;
}
```

### 4. Modal-Based Editing
**Problem:** Keep users in context while editing profiles.

**Solution:**
- **Overlay Design:** Modal doesn't navigate away from dashboard
- **Data Refresh:** Reload profile data after successful edits
- **Error Handling:** Show validation errors within modal

### 5. Responsive Data Display
**Problem:** Handle varying data availability gracefully.

**Solution:**
- **Fallback Values:** Use computed properties with defaults
- **Conditional Rendering:** Show/hide sections based on data availability
- **Loading States:** Display loading indicators during data fetching

```javascript
const displayName = computed(() => {
    return userProfileData.value?.name || auth.user?.name || 'User';
});
```

## Error Handling

### API Error Scenarios:
1. **Authentication failure:** Redirect to login
2. **Authorization failure:** Show appropriate error message
3. **Network errors:** Display retry options
4. **Validation errors:** Show field-specific error messages

### Error Display Strategy:
```vue
<!-- Success Messages -->
<div v-if="successMessage" class="bg-green-100 border-green-500 text-green-700">
    {{ successMessage }}
</div>

<!-- Error Messages -->
<div v-if="errorMessage" class="bg-red-100 border-red-500 text-red-700">
    {{ errorMessage }}
</div>

<!-- Field Validation Errors -->
<div v-if="validationErrors.email" class="text-red-600 text-sm">
    {{ validationErrors.email[0] }}
</div>
```

## Performance Optimizations

### 1. Computed Properties
**Benefit:** Reactive data display without manual updates.
```javascript
const displayName = computed(() => {
    return userProfileData.value?.name || auth.user?.name || 'User';
});
```

### 2. Conditional Data Fetching
**Benefit:** Only fetch NGO data for NGO staff.
```javascript
if (staffData.role === 'ngo_staff') {
    // Only then fetch NGO data
}
```

### 3. Change Detection
**Benefit:** Minimize API calls by only sending changed fields.

### 4. Modal State Management
**Benefit:** Avoid re-mounting components unnecessarily.

## Security Considerations

### 1. Role-Based Access Control
- **Backend validation:** Server verifies user permissions
- **Frontend restrictions:** UI elements hidden but not relied upon for security
- **API endpoints:** Different endpoints for different user types

### 2. Data Validation
- **Frontend validation:** Immediate user feedback
- **Backend validation:** Authoritative data verification
- **Error handling:** Secure error messages that don't leak information

### 3. Authentication State
- **Token management:** Handled by auth store
- **Session validation:** Check authentication before API calls
- **Automatic refresh:** Update profile data after successful edits

## Testing Considerations

### Component Testing:
1. **Role detection:** Test with different user types
2. **Profile toggling:** Verify NGO staff can switch views
3. **Form validation:** Test with invalid inputs
4. **Modal behavior:** Test opening, closing, and data persistence

### Integration Testing:
1. **API integration:** Test with real backend responses
2. **Authentication flow:** Test with different auth states
3. **Data persistence:** Verify changes are saved correctly
4. **Error scenarios:** Test network failures and validation errors

### User Experience Testing:
1. **Loading states:** Ensure smooth data loading experience
2. **Error feedback:** Clear error messages and recovery options
3. **Responsive design:** Test on different screen sizes
4. **Accessibility:** Keyboard navigation and screen reader support

## Future Enhancements

### Potential Features:
1. **Profile pictures:** Image upload and management
2. **Activity history:** Track profile changes and login history
3. **Multi-factor authentication:** Enhanced security options
4. **Profile completeness:** Progress indicators for profile completion
5. **Social links:** Integration with social media profiles

### Technical Improvements:
1. **Real-time updates:** WebSocket integration for live profile changes
2. **Offline support:** Cache profile data for offline viewing
3. **Advanced validation:** Real-time field validation and suggestions
4. **Data export:** Allow users to download their profile data
5. **Profile themes:** Customizable profile appearance options
