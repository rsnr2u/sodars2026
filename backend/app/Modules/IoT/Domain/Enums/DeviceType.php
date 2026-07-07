<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Enums;

enum DeviceType: string
{
    case GpsTracker = 'GPS_TRACKER';
    case MediaPlayer = 'MEDIA_PLAYER';
    case LedController = 'LED_CONTROLLER';
    case Camera = 'CAMERA';
    case TemperatureSensor = 'TEMPERATURE_SENSOR';
    case WeatherSensor = 'WEATHER_SENSOR';
    case PowerMonitor = 'POWER_MONITOR';
    case NetworkGateway = 'NETWORK_GATEWAY';
    case Custom = 'CUSTOM';
}
