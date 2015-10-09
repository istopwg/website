--
-- "selfcert.sql"
--
-- Database schema for the PWG self-certification database.
--


--
-- Schema for table 'printer'
--
-- This table tracks the approved IPP Everywhere printers.
--

DROP TABLE IF EXISTS printer;
CREATE TABLE printer (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- Printer ID
  organization_id INTEGER,		-- Organization ID
  product_family VARCHAR(255) NOT NULL,	-- Product family
  model VARCHAR(255) NOT NULL,		-- Model name
  url VARCHAR(255) NOT NULL,		-- Product/organization URL
  cert_version VARCHAR(255) NOT NULL,	-- Certification version (M.m - YYYY-MM-DD)
  color_supported BOOLEAN DEFAULT FALSE,-- FALSE/0 = B&W, TRUE/1 = color
  duplex_supported BOOLEAN DEFAULT FALSE,
					-- FALSE/0 = simplex, TRUE/1 = duplex
  finishings_supported BOOLEAN DEFAULT FALSE,
					-- FALSE/0 = no finishers, TRUE/1 = has finishers
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that submitted the printer

  INDEX(organization_id),
  INDEX(product_family),
  INDEX(cert_version),
  INDEX(color_supported),
  INDEX(duplex_supported),
  INDEX(finishings_supported),
  INDEX(create_id),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
