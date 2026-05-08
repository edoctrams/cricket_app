USE test_db;


-- =====================================================
-- DROP OLD OBJECTS IF THEY EXIST
-- =====================================================

DROP VIEW IF EXISTS Top_Scorers;
DROP VIEW IF EXISTS Team_Players;
DROP VIEW IF EXISTS Match_Summary;

DROP FUNCTION IF EXISTS Calculate_Strike_Rate;

DROP PROCEDURE IF EXISTS Get_Player_Runs;
DROP PROCEDURE IF EXISTS Generate_Rankings;
DROP PROCEDURE IF EXISTS Cursor_Player_List;

DROP TRIGGER IF EXISTS Prevent_Negative_Runs;
DROP TRIGGER IF EXISTS Log_Player_Delete;
DROP TRIGGER IF EXISTS Prevent_Duplicate_Assignment;

-- =====================================================
-- VIEWS
-- =====================================================

-- Top Scoring Players View

CREATE VIEW Top_Scorers AS
SELECT 
    p.player_id,
    p.name,
    SUM(per.runs) AS total_runs
FROM Player p
JOIN Performance per
ON p.player_id = per.player_id
GROUP BY p.player_id, p.name
ORDER BY total_runs DESC;

-- Team Players View

CREATE VIEW Team_Players AS
SELECT
    t.team_name,
    p.name,
    p.role,
    p.country
FROM Team t
JOIN Plays_For pf
ON t.team_id = pf.team_id
JOIN Player p
ON pf.player_id = p.player_id;

-- Match Summary View

CREATE VIEW Match_Summary AS
SELECT
    m.match_id,
    m.venue,
    m.match_date,
    m.format,
    m.Match_Type,
    COUNT(per.performance_id) AS total_players
FROM Match_Details m
LEFT JOIN Performance per
ON m.match_id = per.match_id
GROUP BY 
    m.match_id,
    m.venue,
    m.match_date,
    m.format,
    m.Match_Type;

-- =====================================================
-- FUNCTIONS
-- =====================================================

DELIMITER $$

-- Function to Calculate Strike Rate

CREATE FUNCTION Calculate_Strike_Rate(
    runs INT,
    balls INT
)
RETURNS DECIMAL(10,2)

DETERMINISTIC

BEGIN

    DECLARE sr DECIMAL(10,2);

    IF balls = 0 THEN
        RETURN 0;
    END IF;

    SET sr = (runs / balls) * 100;

    RETURN ROUND(sr,2);

END $$

DELIMITER ;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

DELIMITER $$

-- Procedure to Get Player Statistics

CREATE PROCEDURE Get_Player_Runs(IN pid INT)

BEGIN

    SELECT
        p.player_id,
        p.name,
        SUM(per.runs) AS total_runs,
        SUM(per.wickets) AS total_wickets
    FROM Player p
    JOIN Performance per
    ON p.player_id = per.player_id
    WHERE p.player_id = pid
    GROUP BY p.player_id, p.name;

END $$

DELIMITER ;

DELIMITER $$

-- Procedure to Generate Rankings Automatically

CREATE PROCEDURE Generate_Rankings()

BEGIN

    DELETE FROM Ranking;

    INSERT INTO Ranking(player_id, format, rank_position)

    SELECT
        player_id,
        'Overall',
        rank_num
    FROM
    (
        SELECT
            player_id,
            SUM(runs) AS total_runs,

            DENSE_RANK() OVER (
                ORDER BY SUM(runs) DESC
            ) AS rank_num

        FROM Performance
        GROUP BY player_id

    ) ranked_players;

END $$

DELIMITER ;

DELIMITER $$

-- Cursor Procedure to Display Player Names

CREATE PROCEDURE Cursor_Player_List()

BEGIN

    DECLARE done INT DEFAULT FALSE;
    DECLARE pname VARCHAR(100);

    DECLARE cur CURSOR FOR
    SELECT name FROM Player;

    DECLARE CONTINUE HANDLER
    FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP

        FETCH cur INTO pname;

        IF done THEN
            LEAVE read_loop;
        END IF;

        SELECT pname AS Player_Name;

    END LOOP;

    CLOSE cur;

END $$

DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

DELIMITER $$

-- Prevent Negative Runs or Wickets

CREATE TRIGGER Prevent_Negative_Runs

BEFORE INSERT
ON Performance

FOR EACH ROW

BEGIN

    IF NEW.runs < 0 THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Runs cannot be negative';

    END IF;

    IF NEW.wickets < 0 THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Wickets cannot be negative';

    END IF;

END $$

DELIMITER ;

DELIMITER $$

-- Store Deleted Player Names in Log Table

CREATE TRIGGER Log_Player_Delete

AFTER DELETE
ON Player

FOR EACH ROW

BEGIN

    INSERT INTO Player_Delete_Log(player_name)
    VALUES(OLD.name);

END $$

DELIMITER ;

DELIMITER $$

-- Prevent Duplicate Team Assignments

CREATE TRIGGER Prevent_Duplicate_Assignment

BEFORE INSERT
ON Plays_For

FOR EACH ROW

BEGIN

    DECLARE cnt INT;

    SELECT COUNT(*)
    INTO cnt
    FROM Plays_For
    WHERE player_id = NEW.player_id
    AND team_id = NEW.team_id;

    IF cnt > 0 THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Player already assigned to this team';

    END IF;

END $$

DELIMITER ;

-- =====================================================
-- SAMPLE TEST QUERIES
-- =====================================================

SELECT * FROM Player;

SELECT * FROM Team;

SELECT * FROM Match_Details;

SELECT * FROM Performance;

SELECT * FROM Ranking;

-- View Query

SELECT * FROM Top_Scorers;

-- Function Query

SELECT 
    p.name,
    per.runs,
    per.balls_faced,
    Calculate_Strike_Rate(per.runs, per.balls_faced) AS strike_rate
FROM Player p
JOIN Performance per
ON p.player_id = per.player_id;

-- Procedure Calls

CALL Get_Player_Runs(1);

CALL Generate_Rankings();

-- View Rankings

SELECT * FROM Ranking;

-- Check Database Objects

SHOW TRIGGERS;

SHOW PROCEDURE STATUS WHERE Db='test_db';

SHOW FUNCTION STATUS WHERE Db='test_db';

-- =====================================================
-- END OF PROJECT
-- =====================================================


show triggers;
select * from player;

SET SQL_SAFE_UPDATES = 0;

SELECT * FROM Ranking LIMIT 20;

CREATE TABLE Player_Delete_Log (

    log_id INT AUTO_INCREMENT PRIMARY KEY,

    player_id INT,

    player_name VARCHAR(100),

    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);


SHOW TRIGGERS FROM test_db;