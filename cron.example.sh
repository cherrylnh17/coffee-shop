#!/bin/bash
curl -s -X POST http://localhost/report/cron/order >> /var/log/cron_expire_order.log 2>&1