# Module Structure Template

Every future bounded context added to SODARS must conform to the following directory structure:

```text
app/Modules/NewModule/
├── Module.php (discovery bootstrapper)
├── module.json (metadata registration config)
├── Application/
│   ├── Actions/ (use-case orchestrators)
│   ├── Events/ (domain event payloads)
│   ├── Listeners/ (event consumers)
│   └── Reports/ (exportable reporting files)
├── Domain/
│   ├── Entities/ (Eloquent models extending BaseBusinessModel)
│   ├── Enums/ (backed status/type definitions)
│   ├── ValueObjects/ (immutable primitives)
│   └── Managers/ (lifecycle controllers)
├── Infrastructure/
│   └── Providers/ (NewModuleServiceProvider binding container singletons)
└── Presentation/
    ├── Controllers/ (REST endpoint controllers)
    └── Routes/
        └── v1/
            └── api.php (versioned endpoints route file)
```
