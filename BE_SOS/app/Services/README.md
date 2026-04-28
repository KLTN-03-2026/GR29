# DistanceService

Dịch vụ tính khoảng cách chính xác sử dụng Google Maps Distance Matrix API (chỉ đường bộ, không có fallback).

## Cách sử dụng

### Inject vào Controller

```php
use App\Services\DistanceService;

class YourController extends Controller
{
    protected DistanceService $distanceService;

    public function __construct(DistanceService $distanceService)
    {
        $this->distanceService = $distanceService;
    }
}
```

### Tính khoảng cách giữa 2 điểm

```php
$distance = $this->distanceService->calculateDistance($lat1, $lng1, $lat2, $lng2);
// Trả về float km hoặc null nếu thiếu tọa độ hoặc lỗi API
```

### Tính khoảng cách từ 1 điểm tới nhiều điểm

```php
$destinations = [
    ['key' => 'team_1', 'lat' => 10.5, 'lng' => 106.5],
    ['key' => 'team_2', 'lat' => 10.6, 'lng' => 106.6],
];

$distances = $this->distanceService->calculateDistances($originLat, $originLng, $destinations);
// Trả về ['team_1' => 5.2, 'team_2' => 3.1] (chỉ các điểm thành công)
```

### Cho Admin điều phối

Tính khoảng cách từ vị trí yêu cầu tới các đội:

```php
$teamDistances = $this->distanceService->calculateDistances($requestLat, $requestLng, $teamsArray);
```

### Cho Rescuer

Tính khoảng cách từ vị trí rescuer tới yêu cầu:

```php
$distanceToRequest = $this->distanceService->calculateDistance($rescuerLat, $rescuerLng, $requestLat, $requestLng);
```

## Cấu hình

**BẮT BUỘC** thêm Google Maps API key vào `.env`:

```
GOOGLE_MAPS_API_KEY=your_api_key_here
```

**Quan trọng**: Không có fallback Haversine. Nếu không có API key hoặc lỗi API, sẽ trả về `null` hoặc mảng rỗng.

## Phương thức

- `calculateDistance($fromLat, $fromLng, $toLat, $toLng)`: Khoảng cách 2 điểm
- `calculateDistances($originLat, $originLng, $destinations)`: Khoảng cách tới nhiều điểm
- `getDistanceFromRescuerToRequest($rescuerLat, $rescuerLng, $requestLat, $requestLng)`: Wrapper cho rescuer
- `getDistancesFromRequestToTeams($requestLat, $requestLng, $teams)`: Wrapper cho admin (expect teams có 'id', 'lat', 'lng')
