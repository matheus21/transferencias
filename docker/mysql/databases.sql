CREATE
DATABASE IF NOT EXISTS `transferencias`;
CREATE
DATABASE IF NOT EXISTS `transferencias_testes`;

CREATE
USER 'root'@'127.0.0.1' IDENTIFIED BY 'changeme';
GRANT ALL
ON *.* TO 'root'@'%';