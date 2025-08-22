# NGO Staff Features: Profile Management & Bangladesh Campaign Map

## Overview

This document provides comprehensive documentation for NGO staff specific features in the HelpRelief application, covering both ProfileView component enhancements and the interactive Bangladesh Map feature for campaign visualization.

## Table of Contents

1. [ProfileView Component & Access Control](#profileview-component--access-control)
2. [Bangladesh Campaign Map Feature](#bangladesh-campaign-map-feature)
3. [Architecture & Integration](#architecture--integration)
4. [Implementation Details](#implementation-details)
5. [User Experience Workflows](#user-experience-workflows)
6. [API Dependencies](#api-dependencies)
7. [Security & Access Control](#security--access-control)
8. [Testing & Troubleshooting](#testing--troubleshooting)

## ProfileView Component & Access Control

### Issues Fixed

#### 1. ProfileView NGO Profile Fetching Issue

**Problem**: ProfileView was failing to fetch NGO profile data for NGO staff users.

**Root Cause**: 
- The API call to fetch NGO details was failing
- Limited error handling for missing NGO data
- TypeScript errors for missing user properties

**Solution**:
- Enhanced error handling in NGO data fetching
- Added fallback logic to use NGO data from staff response if available
- Fixed TypeScript issues by handling missing properties gracefully
- Improved UI feedback for NGO data not available scenarios

**Changes Made**:
```typescript
// Enhanced NGO data fetching with fallback
if (staffData.ngo) {
  ngoData.value = staffData.ngo;
} else {
  // Fallback: Fetch NGO details separately
  try {
    const ngoRes = await api.get(`/ngos/${staffData.ngo_id}`);
    ngoData.value = ngoRes.data;
  } catch (ngoError) {
    // Graceful degradation with basic info
    ngoData.value = {
      id: staffData.ngo_id,
      name: 'NGO Information',
      email: 'Contact NGO for details'
    };
  }
}
```

#### 2. Dashboard Access Control Implementation

**Problem**: CampaignMap component was visible to all users, but it should only be available for NGO staff.

**Solution**:
- Added conditional rendering based on NGO staff status
- Implemented responsive grid layout that adapts to user type
- Added informative placeholder for non-NGO users

**Changes Made**:

##### UserDashboard.vue
```vue
<!-- Dynamic grid layout based on user type -->
<div class="grid grid-cols-1" :class="isNgoStaff ? 'lg:grid-cols-3' : 'lg:grid-cols-2'">
  <!-- Profile View -->
  <div class="lg:col-span-1 h-full">
    <ProfileView class="h-full" />
  </div>

  <!-- Live Feeds -->
  <div class="lg:col-span-1 space-y-6 h-full overflow-y-auto">
    <OngoingDisasters />
    <OngoingCampaigns />
  </div>

  <!-- Campaign Map (NGO Staff Only) -->
  <div v-if="isNgoStaff" class="lg:col-span-1 h-full">
    <CampaignMap class="h-full" />
  </div>

  <!-- Feature Notice for Non-NGO Users -->
  <div v-else class="lg:col-span-1 h-full">
    <div class="bg-white p-6 rounded-lg shadow-lg h-full flex flex-col items-center justify-center text-center">
      <!-- Map icon and informative message -->
    </div>
  </div>
</div>
```

## Bangladesh Campaign Map Feature

### Architecture Overview

The Bangladesh Map Feature follows a modern web application architecture with clear separation of concerns:

- **Backend (Laravel/PHP)**: Provides RESTful API endpoints for campaign intensity data
- **Frontend (Vue.js 3 + TypeScript)**: Interactive map component with modal functionality
- **Map Library**: vue3-svg-map for SVG-based interactive maps
- **Styling**: Tailwind CSS for responsive design

### Key Components Structure

```
├── Backend
│   └── MapController.php (API endpoints)
├── Frontend
│   ├── CampaignMap.vue (Main component)
│   ├── bangladesh.js (Map data)
│   ├── vue3-svg-map.d.ts (TypeScript definitions)
│   └── bangladeshHigh.svg (High-quality SVG)
└── Integration
    └── UserDashboard.vue (Parent component)
```

### Backend Implementation

#### MapController.php

**Location**: `backend/app/Http/Controllers/MapController.php`

**Purpose**: Provides API endpoints for campaign intensity data specific to NGO operations.

##### Key Methods

###### 1. getCampaignIntensityByState()

```php
public function getCampaignIntensityByState(Request $request)
```

**Functionality**:
- Validates NGO staff authentication
- Aggregates campaign data by Bangladesh administrative divisions
- Returns intensity levels (none, low, medium, high) based on campaign counts

**Logic**:
```php
// Campaign intensity calculation
$intensity = 'none';
if ($count >= 10) $intensity = 'high';
else if ($count >= 5) $intensity = 'medium';
else if ($count >= 1) $intensity = 'low';
```

###### 2. getStateDetails()

```php
public function getStateDetails($state, Request $request)
```

**Functionality**:
- Provides detailed campaign information for a specific division
- Returns active campaigns with disaster types and dates
- Maintains NGO-specific data isolation

### Frontend Implementation

#### CampaignMap.vue

**Location**: `frontend/src/components/CampaignMap.vue`

**Purpose**: Interactive map component with dual-view functionality (compact dashboard view + detailed modal).

##### Component Structure

**Template Architecture**:
1. **Compact Dashboard View**
   - Small map preview (h-64)
   - Click-to-expand functionality
   - Hover effects for user engagement

2. **Interactive Modal View**
   - Full-screen modal overlay
   - Large interactive map (h-96)
   - Event handlers for user interactions
   - State details panel

##### Key Functions

```typescript
// Core reactive data
const showModal = ref(false)
const districtData = ref<DistrictData[]>([])
const selectedDistrict = ref<DistrictData | null>(null)
const hoveredDistrict = ref<string | null>(null)

// Key functionality
const fetchCampaignData = async () => {
  try {
    const response = await api.get('/map/campaign-intensity')
    updateDistrictData(response.data.data)
  } catch (error) {
    console.error('Error fetching campaign data:', error)
  }
}

// Enhanced location class function with proper ID mapping
const getLocationClass = (location: { id: string; name: string }) => {
  // Map location ID to district name
  const districtMap: Record<string, string> = {
    'BD-A': 'Barisal',
    'BD-B': 'Chittagong',
    'BD-C': 'Dhaka',
    'BD-D': 'Khulna',
    'BD-E': 'Rajshahi',
    'BD-F': 'Rangpur',
    'BD-G': 'Sylhet'
  }
  
  const districtName = districtMap[location.id] || location.name
  const district = getDistrictByName(districtName)
  const campaignCount = district?.campaign_count || 0

  let intensityClass = 'campaign-intensity-none'
  if (campaignCount >= 6) intensityClass = 'campaign-intensity-high'
  else if (campaignCount >= 3) intensityClass = 'campaign-intensity-medium'
  else if (campaignCount >= 1) intensityClass = 'campaign-intensity-low'

  return `svg-map__location ${intensityClass}`
}
```

##### Styling System

CSS classes for campaign intensity visualization with enhanced specificity:

```css
/* Base styles for svg-map locations */
.svg-map__location {
  fill: #e5e7eb !important; /* gray-200 */
  stroke: #374151; /* gray-700 */
  stroke-width: 1;
  cursor: pointer;
  transition: all 0.3s ease;
}

.svg-map__location:hover {
  stroke-width: 2;
  filter: brightness(0.9);
}

/* Campaign intensity colors with higher specificity */
.svg-map__location.campaign-intensity-none {
  fill: #e5e7eb !important; /* gray-200 */
}

.svg-map__location.campaign-intensity-none:hover {
  fill: #bbf7d0 !important; /* green-200 - light green for no campaigns */
  filter: none; /* Remove brightness filter for green hover */
}

.svg-map__location.campaign-intensity-low {
  fill: #bfdbfe !important; /* blue-200 */
}

.svg-map__location.campaign-intensity-medium {
  fill: #3b82f6 !important; /* blue-500 */
}

.svg-map__location.campaign-intensity-high {
  fill: #1e40af !important; /* blue-800 */
}
```

**Campaign Intensity Thresholds**:
- **None (Gray)**: 0 campaigns
- **Low (Light Blue)**: 1-2 campaigns
- **Medium (Blue)**: 3-5 campaigns
- **High (Dark Blue)**: 6+ campaigns

**Hover Effects**:
- **No Campaigns**: Gray → Light green on hover (indicates potential/available areas)
- **With Campaigns**: Blue shades → Slightly darker blue on hover (indicates active areas)
- **Smooth Transitions**: 0.3s ease transition for polished user experience

### Bangladesh Administrative Divisions

| ID | Division | Administrative Code |
|----|----------|-------------------|
| BD-A | Barisal | Barisal Division |
| BD-B | Chittagong | Chittagong Division |
| BD-C | Dhaka | Dhaka Division |
| BD-D | Khulna | Khulna Division |
| BD-E | Rajshahi | Rajshahi Division |
| BD-F | Rangpur | Rangpur Division |
| BD-G | Sylhet | Sylhet Division |

## Technical Decisions & Rationale

### 1. Library Selection: vue3-svg-map

**Reasons**:
- **Performance**: SVG-based rendering for smooth interactions
- **Customization**: Full control over styling and behavior
- **Accessibility**: Built-in keyboard navigation support
- **Bundle Size**: Lightweight compared to full GIS libraries

**Alternatives Considered**:
- Leaflet.js: Too heavy for simple choropleth needs
- D3.js: Steep learning curve, overkill for requirements
- Custom SVG: Reinventing the wheel

### 2. TypeScript Integration

**Benefits**:
- **Type Safety**: Prevents runtime errors in map data handling
- **Developer Experience**: IntelliSense and autocompletion
- **Maintainability**: Self-documenting code structure

**Implementation**:
```typescript
// Custom type definitions
interface DistrictData {
  name: string
  campaign_count: number
  intensity: 'none' | 'low' | 'medium' | 'high'
}
```

### 3. Modal vs. Navigation Approach

**Decision**: Modal-based interaction

**Rationale**:
- **Context Preservation**: Users maintain dashboard context
- **Performance**: No route changes or page reloads
- **User Experience**: Seamless interaction flow
- **Mobile Friendly**: Overlay works well on all screen sizes

### 4. API Design Principles

**RESTful Endpoints**:
- `GET /map/campaign-intensity`: List view data
- `GET /map/state-details/{state}`: Detail view data

**Benefits**:
- **Cacheable**: GET requests can be cached
- **Predictable**: Standard REST conventions
- **Scalable**: Easy to extend with additional parameters

### 5. Color Scheme Selection

**Chosen**: Blue gradient (gray → light blue → blue → dark blue)

**Rationale**:
- **Accessibility**: High contrast ratios
- **Intuitive**: Darker = more intense
- **Professional**: Appropriate for NGO context
- **Color-blind Friendly**: Relies on lightness variations

## Key Improvements

### 1. Better Error Handling
- Graceful degradation when NGO API calls fail
- Informative error messages for users
- Fallback UI states for missing data

### 2. Enhanced User Experience
- Clear visual distinction between user types
- Informative placeholders for restricted features
- Responsive layout that adapts to available features

### 3. TypeScript Compatibility
- Fixed type errors for missing user properties
- Better type safety for optional fields
- Graceful handling of undefined values

### 4. Security & Access Control
- Map feature properly restricted to NGO staff
- Clear visual indication of feature availability
- Maintains user context while showing restrictions

## User Experience Flow

### For NGO Staff:
1. Login → Dashboard loads with 3-column layout
2. ProfileView shows toggle between User/NGO profiles
3. NGO profile data loads with fallback handling
4. Campaign Map is fully functional and interactive

### For Regular Users:
1. Login → Dashboard loads with 2-column layout
2. ProfileView shows only user profile (no toggle)
3. Map area shows informative placeholder
4. Clear messaging about NGO staff exclusive features

### Map Interaction Workflow

#### Dashboard Integration

1. **Initial Load**
   - Component mounts on NGO staff dashboard
   - Automatically fetches campaign intensity data
   - Renders compact map view with color-coded divisions

2. **Dashboard View Interactions**
   - **Hover**: Visual feedback with shadow effects
   - **Click**: Opens interactive modal

3. **Modal View Interactions**
   - **District Hover**: 
     - Shows district name tooltip
     - **No campaigns**: Light green highlight indicating potential areas
     - **With campaigns**: Darker shade with thicker border indicating active areas
   - **District Click**: Loads detailed campaign information
   - **Outside Click**: Closes modal
   - **Close Button**: Explicit modal dismissal

### Visual Feedback System

- **Loading States**: Skeleton loaders during data fetch
- **Error Handling**: User-friendly error messages
- **Empty States**: Informative messages when no data available
- **Interactive Hover Effects**: 
  - **Districts with no campaigns**: Light green hover effect to indicate potential areas for new campaigns
  - **Districts with campaigns**: Darker blue hover effect with thicker borders to indicate active areas
  - **Smooth transitions**: 0.3s ease animation for polished user experience
- **Click Feedback**: Visual confirmation when districts are selected

## API Dependencies

### Required Endpoints:
- `GET /ngo-staff` - Returns staff info with optional NGO data
- `GET /ngos/{id}` - Fallback for NGO details (optional)
- `GET /map/campaign-intensity` - Map data (NGO staff only)
- `GET /map/state-details/{state}` - Detail view data

### API Response Formats:

#### NGO Staff Response:
```json
{
  "ngo_id": 1,
  "role": "ngo_staff", 
  "designation": "Coordinator",
  "privilege_role": "manager",
  "ngo": {
    "id": 1,
    "name": "Example NGO",
    "email": "contact@example-ngo.org",
    "phone": "+1234567890",
    "address": "123 NGO Street",
    "type": "Relief Organization",
    "registration_number": "NGO123456",
    "description": "Helping communities in need",
    "website": "https://example-ngo.org",
    "established_date": "2020-01-01"
  }
}
```

#### Campaign Intensity Response:
```json
{
  "data": [
    {
      "state": "Dhaka",
      "campaign_count": 15,
      "intensity": "high"
    }
  ]
}
```

#### State Details Response:
```json
{
  "state": "Dhaka",
  "total_campaigns": 15,
  "campaigns": [
    {
      "id": 1,
      "disaster_name": "Dhaka Flood Relief",
      "disaster_type": "Flood",
      "start_date": "2024-01-15"
    }
  ]
}
```

### Security Features

- **Authentication**: Uses Sanctum middleware for API protection
- **Authorization**: Validates NGO staff membership
- **Data Isolation**: Each NGO only sees their own campaign data

## Testing & Troubleshooting

### Testing Checklist

#### NGO Staff User:
- [ ] Can view both user and NGO profiles
- [ ] NGO profile toggle works correctly
- [ ] NGO data displays properly
- [ ] Campaign map is visible and functional
- [ ] Dashboard has 3-column layout
- [ ] Map modal opens and closes properly
- [ ] District details load correctly

#### Regular User:
- [ ] Only sees user profile (no toggle)
- [ ] Map area shows informative placeholder
- [ ] Dashboard has 2-column layout
- [ ] No access to NGO-specific features

#### Error Scenarios:
- [ ] Handles NGO API failures gracefully
- [ ] Shows appropriate error messages
- [ ] Fallback UI states work correctly
- [ ] No console errors for missing properties

### Common Issues & Solutions

#### 1. CSS Import Errors

**Problem**: `Missing "./dist/index.css" specifier in "vue3-svg-map" package`

**Solution**: Use correct CSS import path:
```typescript
import "vue3-svg-map/style.css"  // Correct
// Not: import "vue3-svg-map/dist/index.css"
```

#### 2. TypeScript Errors

**Problem**: `Cannot find module 'vue3-svg-map'`

**Solution**: Ensure type definitions exist:
```typescript
// src/types/vue3-svg-map.d.ts
declare module 'vue3-svg-map' {
  import { Component } from 'vue'
  export const SvgMap: Component<{...}>
}
```

#### 3. Map Not Rendering

**Problem**: SVG map appears blank or corrupted

**Checklist**:
- Verify `bangladesh.js` export structure
- Check `viewBox` coordinates
- Validate SVG path data syntax
- Ensure proper CSS classes are applied

#### 4. Map Colors Not Updating

**Problem**: Campaign intensity colors not reflecting on the map despite correct data

**Root Cause**: 
- Incorrect location ID mapping in `getLocationClass` function
- CSS specificity issues with SVG styling
- vue3-svg-map passes location IDs (BD-A, BD-B) not district names

**Solution**:
```typescript
// Correct ID to district mapping
const districtMap: Record<string, string> = {
  'BD-A': 'Barisal',
  'BD-B': 'Chittagong',
  'BD-C': 'Dhaka',
  'BD-D': 'Khulna',
  'BD-E': 'Rajshahi',
  'BD-F': 'Rangpur',
  'BD-G': 'Sylhet'
}

const districtName = districtMap[location.id] || location.name
```

**CSS Fix**:
```css
/* Use higher specificity and !important for SVG override */
.svg-map__location.campaign-intensity-high {
  fill: #1e40af !important;
}
```

#### 5. API Authentication Issues

**Problem**: 403 Unauthorized errors

**Solution**:
- Verify user is authenticated with Sanctum
- Confirm user has NGO staff record
- Check CORS configuration for frontend domain

#### 6. Modal Interaction Problems

**Problem**: Modal doesn't close or map clicks don't work

**Solution**:
- Verify `@click.stop` on modal content
- Check z-index values for proper layering
- Ensure event handlers are properly bound

## Recent Fixes & Updates

### Campaign Map Color Intensity Fix (August 2025)

**Issue**: Campaign intensity colors were not displaying correctly on the map despite having correct campaign data.

**Root Causes Identified**:
1. **Location ID Mapping**: The `getLocationClass` function was using `location.name` directly, but vue3-svg-map passes location IDs (BD-A, BD-B, etc.)
2. **CSS Specificity**: Campaign intensity CSS classes weren't specific enough to override default SVG styles
3. **Missing District Mapping**: No mapping between SVG location IDs and actual district names

**Solutions Implemented**:

1. **Enhanced Location Class Function**:
```typescript
const getLocationClass = (location: { id: string; name: string }) => {
  // Map location ID to district name
  const districtMap: Record<string, string> = {
    'BD-A': 'Barisal', 'BD-B': 'Chittagong', 'BD-C': 'Dhaka',
    'BD-D': 'Khulna', 'BD-E': 'Rajshahi', 'BD-F': 'Rangpur', 'BD-G': 'Sylhet'
  }
  
  const districtName = districtMap[location.id] || location.name
  const district = getDistrictByName(districtName)
  const campaignCount = district?.campaign_count || 0

  let intensityClass = 'campaign-intensity-none'
  if (campaignCount >= 6) intensityClass = 'campaign-intensity-high'
  else if (campaignCount >= 3) intensityClass = 'campaign-intensity-medium'
  else if (campaignCount >= 1) intensityClass = 'campaign-intensity-low'

  return `svg-map__location ${intensityClass}`
}
```

2. **Improved CSS Specificity**:
```css
.svg-map__location.campaign-intensity-none { fill: #e5e7eb !important; }
.svg-map__location.campaign-intensity-low { fill: #bfdbfe !important; }
.svg-map__location.campaign-intensity-medium { fill: #3b82f6 !important; }
.svg-map__location.campaign-intensity-high { fill: #1e40af !important; }
```

3. **Debug Logging**: Added console logging to track district processing and campaign counts

**Result**: Map now correctly displays color-coded intensity based on campaign counts per district.

### Hover Effect Enhancement (August 2025)

**Enhancement**: Added distinctive hover effects to improve user experience and visual feedback.

**Implementation**:
```css
/* Green hover for districts with no campaigns */
.svg-map__location.campaign-intensity-none:hover {
  fill: #bbf7d0 !important; /* green-200 */
  filter: none;
}

/* Darker hover for districts with campaigns */
.svg-map__location:hover {
  stroke-width: 2;
  filter: brightness(0.9);
}
```

**User Experience**:
- **Green hover effect**: Indicates districts available for new campaigns (no current campaigns)
- **Darker blue hover effect**: Indicates districts with active campaigns
- **Smooth transitions**: 0.3s ease animation for professional feel

**Purpose**: Provides clear visual distinction between available and active campaign areas during user interaction.

## Files Modified

1. `frontend/src/components/ProfileView.vue`
   - Enhanced NGO data fetching with error handling
   - Fixed TypeScript compatibility issues
   - Improved UI for missing data scenarios

2. `frontend/src/views/UserDashboard.vue`
   - Added conditional map rendering for NGO staff
   - Implemented responsive grid layout
   - Added informative placeholder for regular users

3. `frontend/src/components/CampaignMap.vue`
   - Main map component with dual-view functionality
   - Interactive modal system
   - Campaign intensity visualization

4. `backend/app/Http/Controllers/MapController.php`
   - API endpoints for map data
   - NGO staff authentication and authorization
   - Campaign intensity calculation

## Setup & Configuration

### Backend Setup

#### Install Dependencies
```bash
cd backend
composer install
```

#### Database Migration
```bash
php artisan migrate
```

#### API Routes
Routes are automatically registered in `routes/api.php`:
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/map/campaign-intensity', [MapController::class, 'getCampaignIntensityByState']);
    Route::get('/map/state-details/{state}', [MapController::class, 'getStateDetails']);
});
```

### Frontend Setup

#### Install Dependencies
```bash
cd frontend
npm install vue3-svg-map
```

#### Import Component
```vue
<script setup>
import CampaignMap from '@/components/CampaignMap.vue'
</script>

<template>
  <CampaignMap />
</template>
```

### Environment Configuration

#### Backend (.env)
```env
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

#### Frontend (.env)
```env
VITE_API_BASE_URL=http://localhost:8000/api
```

## Future Enhancements

### ProfileView Enhancements

1. **Role-Based Feature Access**
   - Extend to other NGO-specific features
   - Granular permissions based on NGO roles

2. **User Onboarding**
   - Guide for joining NGOs
   - Feature discovery for new users

3. **Progressive Enhancement**
   - Lazy load NGO-specific components
   - Conditional feature loading based on user type

### Map Feature Enhancements

1. **Real-time Updates**
   - WebSocket integration for live campaign data
   - Auto-refresh functionality

2. **Advanced Filtering**
   - Date range selection
   - Disaster type filtering
   - Campaign status filtering

3. **Export Functionality**
   - Download map as PNG/SVG
   - Export campaign data as CSV

4. **Mobile Optimization**
   - Touch-friendly interactions
   - Responsive map sizing
   - Swipe gestures for modal

5. **Accessibility Improvements**
   - Screen reader support
   - Keyboard navigation
   - High contrast mode

---

**Document Version**: 2.0  
**Last Updated**: August 2025  
**Authors**: Development Team  
**Status**: Production Ready
