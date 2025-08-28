# Aid Need Density Visualization Feature

## Overview
This feature provides NGO staff with a comprehensive visualization of aid need density across different regions, helping prioritize response areas and volunteer task assignments.

## Features

### 1. Three Map Modes
- **Campaign Coverage Map**: Shows active disaster campaigns by region
- **Aid Request Heatmap**: Displays total aid requests by urgency level
- **Aid Need Density Map**: Visualizes unmet aid needs (Requests - Support)

### 2. Aid Need Calculation
The system calculates aid need using the formula:
```
Aid Need = Aid Requests - Aid Support
```

This provides a clear picture of where help is still needed versus where support has already been provided.

### 3. Deployment Zone Recommendations
- **Priority Levels**: Critical, High, Medium, Low based on aid need and urgency
- **Recommended Volunteers**: Calculated based on aid need volume and urgency scores
- **Task Types**: Suggested activities based on priority level

### 4. Volunteer Task Prioritization
- **Task Recommendations**: Prioritized by region and urgency
- **Duration Estimates**: Based on aid need volume
- **Resource Allocation**: Optimal volunteer distribution across zones

## Technical Implementation

### Backend (Laravel)
- **Endpoint**: `/api/map/aid-need` (authenticated)
- **Test Endpoint**: `/api/test/aid-need` (unauthenticated, for development)
- **Controller**: `MapController@getAidNeedByState`

### Frontend (Vue.js)
- **Component**: `CampaignMap.vue`
- **Toggle Button**: Cycles through three visualization modes
- **Interactive Map**: Click regions to see detailed information
- **Modal Display**: Shows deployment zones and volunteer task recommendations

### Data Structure
```json
{
  "states": [
    {
      "name": "Dhaka",
      "request_count": 15,
      "support_count": 8,
      "aid_needed": 7,
      "urgency_score": 18,
      "breakdown": {
        "low": 3,
        "medium": 5,
        "high": 4,
        "critical": 3
      },
      "intensity": 0.75
    }
  ],
  "deployment_zones": [
    {
      "state": "Dhaka",
      "aid_needed": 7,
      "urgency_score": 18,
      "priority_level": "high",
      "recommended_volunteers": 5
    }
  ],
  "volunteer_tasks": [
    {
      "state": "Dhaka",
      "priority_level": "high",
      "recommended_volunteers": 5,
      "task_types": ["food_distribution", "medical_aid", "shelter_setup", "logistics"],
      "estimated_duration": "2-3 days"
    }
  ]
}
```

## Usage

### For NGO Staff
1. Navigate to the dashboard
2. Click on the Campaign Map component
3. Use the toggle button to switch between visualization modes
4. Click on regions to see detailed information
5. Review deployment zone recommendations
6. Plan volunteer task assignments based on prioritization

### Map Color Coding
- **Aid Need Intensity**:
  - Gray: No need (0)
  - Yellow: Low need (0.01-0.33)
  - Orange: Medium need (0.34-0.66)
  - Red: High need (0.67-1.0)

## Priority Calculation

### Urgency Score
```
Urgency Score = (Low × 1) + (Medium × 2) + (High × 3) + (Critical × 4)
```

### Priority Level
```
Total Score = Aid Needed + (Urgency Score × 0.5)

- Critical: ≥ 15
- High: ≥ 10
- Medium: ≥ 5
- Low: < 5
```

### Volunteer Recommendation
```
Base Volunteers = max(1, ceil(Aid Needed ÷ 3))
Urgency Bonus = ceil(Urgency Score ÷ 8)
Total Volunteers = min(20, Base + Bonus)
```

## Development Notes

### Testing
- Use `/api/test/aid-need` endpoint for development
- Change to `/api/map/aid-need` in production
- Test endpoint provides randomized data for UI testing

### Dependencies
- **Backend**: Laravel, MySQL/PostgreSQL
- **Frontend**: Vue.js 3, TypeScript
- **Map**: vue3-svg-map library

### Security
- Production endpoint requires NGO staff authentication
- Test endpoint should be removed before deployment
- All data is scoped to the authenticated NGO

## Future Enhancements
- Real-time updates via WebSockets
- Integration with volunteer management system
- Automated task assignment recommendations
- Historical trend analysis
- Export functionality for reports

## Files Modified
- `backend/app/Http/Controllers/MapController.php` - Added aid need calculation methods
- `backend/routes/api.php` - Added new endpoints
- `frontend/src/components/CampaignMap.vue` - Added aid need visualization mode
- `AID_NEED_FEATURE_README.md` - This documentation

## Installation
1. Ensure backend dependencies are installed: `composer install`
2. Ensure frontend dependencies are installed: `npm install`
3. Run database migrations: `php artisan migrate`
4. Start backend server: `php artisan serve`
5. Start frontend dev server: `npm run dev`

## Testing
1. Backend: Test endpoint at `http://localhost:8000/api/test/aid-need`
2. Frontend: Build and test the CampaignMap component
3. Verify all three map modes work correctly
4. Test deployment zone recommendations display
5. Verify volunteer task prioritization logic
