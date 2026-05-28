#!/bin/bash
mysql -u nama_user -p'password_database' -D nama_database -e "DELETE FROM nama_tabel WHERE expired_date < NOW();"
