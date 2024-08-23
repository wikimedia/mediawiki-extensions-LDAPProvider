-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: db/ldap.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/ldap_domains (
  domain_id INT AUTO_INCREMENT NOT NULL,
  domain VARBINARY(255) NOT NULL,
  user_id INT NOT NULL,
  INDEX user_id (user_id),
  PRIMARY KEY(domain_id)
) /*$wgDBTableOptions*/;
