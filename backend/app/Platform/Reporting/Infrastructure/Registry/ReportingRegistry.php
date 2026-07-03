<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Registry;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\WidgetProvider;
use App\Platform\Reporting\Domain\Contracts\KpiProvider;
use App\Platform\Reporting\Domain\Contracts\ExportDriver;
use InvalidArgumentException;

class ReportingRegistry
{
    /** @var array<string, string> */
    protected array $reports = [];

    /** @var array<string, string> */
    protected array $widgetProviders = [];

    /** @var array<string, string> */
    protected array $kpis = [];

    /** @var array<string, string> */
    protected array $exportDrivers = [];

    public function registerReport(string $reportClass): void
    {
        if (!is_subclass_of($reportClass, Report::class)) {
            throw new InvalidArgumentException("Class {$reportClass} must implement " . Report::class);
        }
        $this->reports[$reportClass::getKey()] = $reportClass;
    }

    public function resolveReport(string $key): Report
    {
        if (!isset($this->reports[$key])) {
            throw new InvalidArgumentException("Report key '{$key}' not registered.");
        }
        return app($this->reports[$key]);
    }

    public function registerWidgetProvider(string $providerClass): void
    {
        if (!is_subclass_of($providerClass, WidgetProvider::class)) {
            throw new InvalidArgumentException("Class {$providerClass} must implement " . WidgetProvider::class);
        }
        $instance = app($providerClass);
        $this->widgetProviders[$instance->getWidgetType()] = $providerClass;
    }

    public function resolveWidgetProvider(string $type): WidgetProvider
    {
        if (!isset($this->widgetProviders[$type])) {
            throw new InvalidArgumentException("Widget type '{$type}' not registered.");
        }
        return app($this->widgetProviders[$type]);
    }

    public function registerKpi(string $kpiClass): void
    {
        if (!is_subclass_of($kpiClass, KpiProvider::class)) {
            throw new InvalidArgumentException("Class {$kpiClass} must implement " . KpiProvider::class);
        }
        $this->kpis[$kpiClass::getKey()] = $kpiClass;
    }

    public function resolveKpi(string $key): KpiProvider
    {
        if (!isset($this->kpis[$key])) {
            throw new InvalidArgumentException("KPI key '{$key}' not registered.");
        }
        return app($this->kpis[$key]);
    }

    public function getKpis(): array
    {
        return $this->kpis;
    }

    public function registerExportDriver(string $driverClass): void
    {
        if (!is_subclass_of($driverClass, ExportDriver::class)) {
            throw new InvalidArgumentException("Class {$driverClass} must implement " . ExportDriver::class);
        }
        $instance = app($driverClass);
        $this->exportDrivers[$instance->getFormat()] = $driverClass;
    }

    public function resolveExportDriver(string $format): ExportDriver
    {
        if (!isset($this->exportDrivers[$format])) {
            throw new InvalidArgumentException("Export format '{$format}' not registered.");
        }
        return app($this->exportDrivers[$format]);
    }
}
