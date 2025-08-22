# Ongoing Campaigns Feature Documentation

## Overview
The Ongoing Campaigns feature provides a comprehensive system for displaying, managing, and reporting on disaster relief campaigns. It offers different functionalities based on user roles (General Users vs NGO Staff) and includes priority-based campaign display, detailed campaign listings, and volunteer reporting capabilities.

## Architecture Overview

```
UserDashboard
    └── OngoingCampaigns (Top 3 Priority)
            ├── CampaignListModal (All Campaigns)
            └── VolunteerReportModal (NGO Staff Only)
                    ├── Aggregate Reports
                    └── Individual Reports
```

## Backend Components

### 1. Database Structure

#### Tables Involved:
- **`disaster_campaign_assignments`** - Main table linking disasters to NGOs
- **`disasters`** - Disaster information
- **`ngos`** - NGO organization details
- **`users`** - User accounts
- **`ngo_staff`** - NGO staff relationships
- **`volunteer_task_logs`** - Volunteer activity tracking

#### Key Migration: `create_disaster_campaign_assignments_table.php`
```php
Schema::create('disaster_campaign_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('disaster_id')->constrained('disasters')->onDelete('cascade');
    $table->foreignId('ngo_id')->constrained('ngos')->onDelete('cascade'); // Fixed: was 'users'
    $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
    $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
    $table->enum('help_needed', ['low', 'medium', 'high'])->default('medium');
    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamps();
});
```

**Key Fix Applied:** Changed `ngo_id` constraint from `users` table to `ngos` table for proper relationships.

### 2. Models

#### DisasterCampaignAssignment Model
```php
class DisasterCampaignAssignment extends Model
{
    protected $fillable = [
        'disaster_id', 'ngo_id', 'assigned_by', 'status', 'help_needed', 'updated_by'
    ];

    // Relationships
    public function disaster() {
        return $this->belongsTo(Disaster::class, 'disaster_id');
    }

    public function ngo() {
        return $this->belongsTo(Ngo::class, 'ngo_id');
    }

    public function assignedBy() {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
```

### 3. API Controllers

#### CampaignController
**Purpose:** Handles campaign data retrieval for different user types.

**Routes:**
- `GET /campaigns` - All active campaigns (General Users)
- `GET /campaigns/my` - NGO-specific campaigns (NGO Staff)
- `GET /campaigns/{id}` - Specific campaign details

**Key Methods:**

##### `index()` - General Users
```php
public function index()
{
    $campaigns = DisasterCampaignAssignment::with(['disaster', 'ngo'])
        ->where('status', 'active')
        ->get()
        ->map(function ($assignment) {
            return $this->formatCampaignData($assignment);
        });
    return response()->json($campaigns);
}
```

##### `myCampaigns()` - NGO Staff
```php
public function myCampaigns(Request $request)
{
    $user = $request->user();
    $staff = NgoStaff::where('user_id', $user->id)->first();

    if (!$staff) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $campaigns = DisasterCampaignAssignment::with(['disaster', 'ngo'])
        ->where('ngo_id', $staff->ngo_id)
        ->where('status', 'active')
        ->get()
        ->map(function ($assignment) {
            return $this->formatCampaignData($assignment);
        });
    return response()->json($campaigns);
}
```

##### `formatCampaignData()` - Data Formatting
```php
private function formatCampaignData($assignment)
{
    return [
        'id' => $assignment->id,
        'name' => $assignment->disaster->name . ' Campaign',
        'disaster_id' => $assignment->disaster_id,
        'disaster_name' => $assignment->disaster->name ?? 'Unknown Disaster',
        'disaster_type' => $assignment->disaster->type ?? 'Unknown Type',
        'disaster_location' => $assignment->disaster->location ?? 'Unknown Location',
        'ngo_id' => $assignment->ngo_id,
        'ngo_name' => $assignment->ngo->name ?? 'Unknown NGO',
        'status' => $assignment->status,
        'help_needed' => $assignment->help_needed,
        'created_at' => $assignment->created_at,
        'updated_at' => $assignment->updated_at,
    ];
}
```

#### VolunteerReportController
**Purpose:** Provides volunteer performance analytics for NGO staff.

**Routes:**
- `GET /reports/volunteers/aggregate?disaster_id={id}` - Campaign summary stats
- `GET /reports/volunteers/individual?disaster_id={id}` - Individual volunteer details

**Key Methods:**

##### `aggregate()` - Campaign Summary
```php
public function aggregate(Request $request)
{
    $disasterId = $request->query('disaster_id');
    $logs = VolunteerTaskLog::where('disaster_id', $disasterId);

    return response()->json([
        'disaster_id' => (int) $disasterId,
        'total_volunteers' => $logs->distinct('volunteer_id')->count('volunteer_id'),
        'tasks_assigned' => $logs->count(),
        'tasks_completed' => $logs->where('status', 'ended')->count(),
        'completion_rate' => $completionRate,
        'total_hours' => $totalHours
    ]);
}
```

##### `individual()` - Volunteer Details
```php
public function individual(Request $request)
{
    $disasterId = $request->query('disaster_id');
    $logs = VolunteerTaskLog::where('disaster_id', $disasterId)
        ->with('volunteer')
        ->get()
        ->groupBy('volunteer_id');

    $response = $logs->map(function ($group) {
        return [
            'volunteer_id' => $first->volunteer_id,
            'name' => $first->volunteer->name,
            'tasks_assigned' => $group->count(),
            'tasks_completed' => $group->where('status', 'ended')->count(),
            'attendance_days' => $attendanceDays,
            'total_hours' => $hours,
            // ... more fields
        ];
    });

    return response()->json($response);
}
```

### 4. Database Seeders

#### DisasterCampaignAssignmentSeeder
**Purpose:** Creates test data with proper relationships and variety.

```php
public function run()
{
    // NGO 1 Campaigns (High priority)
    DisasterCampaignAssignment::create([
        'disaster_id' => 1, 'ngo_id' => 1, 'assigned_by' => 1,
        'status' => 'active', 'help_needed' => 'high'
    ]);

    // Additional campaigns for testing variety
    DisasterCampaignAssignment::create([
        'disaster_id' => 2, 'ngo_id' => 2, 'assigned_by' => 1,
        'status' => 'active', 'help_needed' => 'low'
    ]);
    // ... more campaigns
}
```

**Seeding Order (DatabaseSeeder.php):**
```php
$this->call([
    UserSeeder::class,
    NgoSeeder::class,                    // Must come before campaign assignments
    DisasterSeeder::class,               // Must come before campaign assignments
    DisasterCampaignAssignmentSeeder::class, // Depends on NGOs and Disasters
    // ... other seeders
]);
```

## Frontend Components

### 1. OngoingCampaigns.vue
**Purpose:** Main component displaying top 3 priority campaigns on dashboard.

#### Key Features:
- **Priority-based sorting** (High > Medium > Low)
- **Role-based data fetching** (All campaigns vs NGO-specific)
- **Modal trigger** for detailed campaign list

#### Component Structure:
```vue
<script setup lang="ts">
interface Campaign {
    id: number;
    name: string;
    disaster_id: number;
    disaster_name: string;
    ngo_name: string;
    help_needed: 'low' | 'medium' | 'high';
    status: string;
}
</script>
```

#### Key Functions:

##### Priority Sorting
```javascript
const topPriorityCampaigns = computed(() => {
    const priorityOrder = { 'high': 3, 'medium': 2, 'low': 1 };
    return [...campaigns.value]
        .sort((a, b) => priorityOrder[b.help_needed] - priorityOrder[a.help_needed])
        .slice(0, 3);
});
```

##### Role Detection & Data Fetching
```javascript
onMounted(async () => {
    try {
        // Check NGO staff status
        const staffRes = await api.get('/ngo-staff');
        if (staffData.role === 'ngo_staff') {
            isNgoStaff.value = true;
            ngoId.value = staffData.ngo_id;
        }

        // Fetch appropriate campaigns
        let endpoint = '/campaigns';
        if (isNgoStaff.value) {
            endpoint = '/campaigns/my';
        }
        const campaignRes = await api.get(endpoint);
        campaigns.value = campaignRes.data;
    } catch (error) {
        errorMessage.value = 'Failed to load campaigns';
    }
});
```

##### Priority Badge Styling
```javascript
const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'high': return 'bg-red-100 text-red-800';
        case 'medium': return 'bg-yellow-100 text-yellow-800';
        case 'low': return 'bg-green-100 text-green-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
```

### 2. CampaignListModal.vue
**Purpose:** Full campaign listing in modal format with role-based actions.

#### Key Features:
- **Complete campaign list** (filtered by role)
- **Priority sorting** for better organization
- **Action buttons** for NGO staff (View Reports)
- **Responsive design** with proper modal behavior

#### Component Structure:
```vue
<script setup lang="ts">
const props = defineProps<{
    campaigns: Campaign[];
    isNgoStaff: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();
</script>
```

#### Key Functions:

##### Campaign Sorting
```javascript
const sortedCampaigns = computed(() => {
    const priorityOrder = { 'high': 3, 'medium': 2, 'low': 1 };
    return [...props.campaigns]
        .sort((a, b) => priorityOrder[b.help_needed] - priorityOrder[a.help_needed]);
});
```

##### Report Modal Trigger (NGO Staff Only)
```javascript
const viewVolunteerReports = (campaign: Campaign) => {
    selectedCampaign.value = campaign;
    showVolunteerReportModal.value = true;
};
```

### 3. VolunteerReportModal.vue
**Purpose:** Comprehensive volunteer analytics dashboard for NGO staff.

#### Key Features:
- **Dual-tab interface** (Aggregate vs Individual)
- **Real-time data fetching** from API
- **Rich data visualization** with color-coded metrics
- **Responsive table design** for individual reports

#### Tab System:
```javascript
const activeTab = ref<'aggregate' | 'individual'>('aggregate');
```

#### Data Fetching:
```javascript
const fetchReports = async () => {
    try {
        // Fetch aggregate data
        const aggRes = await api.get(`/reports/volunteers/aggregate?disaster_id=${props.campaign.disaster_id}`);
        aggregateReport.value = aggRes.data;

        // Fetch individual data
        const indRes = await api.get(`/reports/volunteers/individual?disaster_id=${props.campaign.disaster_id}`);
        individualReports.value = indRes.data;
    } catch (error) {
        errorMessage.value = 'Failed to load reports';
    }
};
```

#### Metrics Display:
```javascript
// Completion rate color coding
const completionRateColor = computed(() => {
    const rate = aggregateReport.value?.completion_rate || 0;
    if (rate >= 80) return 'text-green-600';
    if (rate >= 60) return 'text-yellow-600';
    return 'text-red-600';
});

// Time formatting
const formatHours = (hours: number) => `${hours.toFixed(1)}h`;
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};
```

## User Workflows

### General User Workflow:
1. **Dashboard View:** See top 3 priority campaigns
2. **Campaign List:** Click "View More" to see all active campaigns
3. **Browse:** View campaign details (no reporting access)

### NGO Staff Workflow:
1. **Dashboard View:** See top 3 priority campaigns from their NGO
2. **NGO Campaign List:** Click "View More" to see only their NGO's campaigns
3. **Campaign Details:** Click "View Reports" for specific campaign
4. **Aggregate Reports:** View overall campaign performance metrics
5. **Individual Reports:** Toggle to see individual volunteer performance
6. **Data Analysis:** Use metrics for campaign optimization

## API Integration

### Authentication & Authorization:
```javascript
// Check NGO staff status
const staffRes = await api.get('/ngo-staff');
// Returns: { role: 'ngo_staff', ngo_id: 1 } or 403 error
```

### Campaign Data Endpoints:
```javascript
// General users - all campaigns
GET /campaigns

// NGO staff - their campaigns only
GET /campaigns/my

// Response format:
{
    "id": 1,
    "name": "Cyclone Remal Campaign",
    "disaster_id": 1,
    "disaster_name": "Cyclone Remal",
    "ngo_name": "ngo1",
    "help_needed": "high",
    "status": "active"
}
```

### Volunteer Reports Endpoints:
```javascript
// Aggregate metrics
GET /reports/volunteers/aggregate?disaster_id=1

// Individual volunteer data
GET /reports/volunteers/individual?disaster_id=1
```

## Key Design Decisions

### 1. Role-Based Access Control
- **Separation of concerns:** Different API endpoints for different roles
- **Frontend role detection:** Check NGO staff status on component mount
- **Conditional rendering:** Show/hide features based on user role

### 2. Priority-Based Display
- **Business logic:** High-priority campaigns get prominence
- **User experience:** Most urgent campaigns are always visible
- **Data sorting:** Consistent priority ordering across components

### 3. Modal-Based Navigation
- **Clean UI:** Avoid page navigation for simple data viewing
- **Performance:** Load data on-demand when modal opens
- **User experience:** Quick access to detailed information

### 4. Component Composition
- **Reusability:** Modular components for different use cases
- **Maintainability:** Separate concerns (display, data, actions)
- **Scalability:** Easy to add new features or modify existing ones

## Performance Considerations

### Backend Optimizations:
- **Eager loading:** Use `with(['disaster', 'ngo'])` to avoid N+1 queries
- **Database indexing:** Foreign keys are automatically indexed
- **Query optimization:** Filter by status and NGO at database level

### Frontend Optimizations:
- **Computed properties:** Reactive sorting and filtering
- **Conditional rendering:** Only render components when needed
- **API caching:** Store fetched data to avoid redundant requests

## Error Handling

### Backend Error Responses:
```php
// Unauthorized access
return response()->json(['error' => 'Unauthorized'], 403);

// Generic error
return response()->json(['error' => 'Failed to fetch campaigns'], 500);
```

### Frontend Error Handling:
```javascript
try {
    const response = await api.get('/campaigns');
    campaigns.value = response.data;
} catch (error) {
    errorMessage.value = 'Failed to load campaigns';
    console.error('Campaign fetch error:', error);
}
```

## Testing Considerations

### Database Testing:
- **Seeder variety:** Multiple NGOs, disasters, and priority levels
- **Relationship integrity:** Proper foreign key constraints
- **Data consistency:** Ensure all required relationships exist

### API Testing:
- **Role-based responses:** Test both general user and NGO staff endpoints
- **Data validation:** Verify correct campaign filtering and formatting
- **Error scenarios:** Test unauthorized access and missing data

### Frontend Testing:
- **Component rendering:** Test with different data states
- **User interactions:** Modal opening/closing, tab switching
- **Role-based features:** Verify correct component behavior for each role

## Future Enhancements

### Potential Features:
1. **Campaign filtering:** By location, disaster type, priority
2. **Real-time updates:** WebSocket integration for live data
3. **Export functionality:** PDF/Excel reports for volunteer data
4. **Campaign analytics:** Trends, comparisons, forecasting
5. **Mobile optimization:** Touch-friendly interface improvements

### Scalability Improvements:
1. **Pagination:** For large campaign lists
2. **Search functionality:** Find specific campaigns quickly
3. **Caching strategies:** Redis for frequently accessed data
4. **Background jobs:** For heavy report generation
5. **API rate limiting:** Prevent abuse and ensure stability
