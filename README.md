# 中華電信 簡訊 Emome Notifier

Provides Emome integration for Symfony Notifier.

## Register the Transport

.env
```
EMOME_DSN=emome://Account:Password@default
```

config\services.yaml
```
    WakeaspInc\Emome\EmomeTransportFactory:
        parent: 'notifier.transport_factory.abstract'
        tags: [ 'texter.transport_factory' ]
```


config\packages\notifier.yaml
```
    ...
        texter_transports:
            emome: '%env(EMOME_DSN)%'
    ...
```
