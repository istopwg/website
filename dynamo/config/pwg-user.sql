--
-- "pwg-user.sql"
--
-- Database schema for the user table (and test users) for the PWG web pages.
--


--
-- Site Features:
--
--   - Users
--   - News/announcements
--   - Issues
--   - Certified printers
--   - Pending certifications (submissions)
--   - Comments (attached to pretty much anything, although initially just for
--     issues and certification stuff)
--


--
-- Schema for table 'user'
--
-- This table lists the registered users for the site.  Various pages use
-- this table when doing login/logout stuff and when listing the available
-- users to assign stuff to.
--

DROP TABLE IF EXISTS user;
CREATE TABLE user (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- ID
  status INTEGER DEFAULT 1,		-- 0 = banned, 1 = pending, 2 = enabled, 3 = deleted
  email VARCHAR(255) UNIQUE NOT NULL,	-- Email address
  name VARCHAR(255) NOT NULL,		-- Real name
  organization_id INTEGER NOT NULL,	-- Organization
  hash CHAR(128) NOT NULL,		-- crypt(password,sha512salt)
  is_admin BOOLEAN DEFAULT FALSE,	-- FALSE/0 = user, TRUE/1 = admin
  is_editor BOOLEAN DEFAULT FALSE,	-- FALSE/0 = not editor, TRUE/1 = editor
  is_member BOOLEAN DEFAULT FALSE,	-- FALSE/0 = not PWG member, TRUE/1 = PWG member
  is_reviewer BOOLEAN DEFAULT FALSE,	-- FALSE/0 = cannot review, TRUE/1 = can review
  is_submitter BOOLEAN DEFAULT FALSE,	-- FALSE/0 = cannot submit, TRUE/1 = can submit
  timezone VARCHAR(255) NOT NULL,	-- Timezone for user
  itemsperpage INTEGER DEFAULT 10 NOT NULL,
					-- Default items per page
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER,			-- User that created the user
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER,			-- User that made the last change

  INDEX(organization_id),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- These are all test accounts with the password "Printing123".
-- (Not present on the production servers...)
INSERT INTO user VALUES(1, 2, 'webmaster@pwg.org','PWG Webmaster',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',1,1,1,1,1,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(2, 2, 'wwwtestuser@pwg.org','PWG Test User',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,0,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(3, 2, 'wwwtesteditor@pwg.org','PWG Test Editor',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,1,0,0,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(4, 2, 'wwwtestmember@pwg.org','PWG Test Member',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,1,0,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(5, 2, 'wwwtestreview1@pwg.org','PWG Test Reviewer 1',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,1,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(5, 2, 'wwwtestreview2@pwg.org','PWG Test Reviewer 2',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,1,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(6, 2, 'wwwtestsubmit@pwg.org','PWG Test Submitter',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,0,1,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);


--
-- End of "pwg-user.sql".
--
