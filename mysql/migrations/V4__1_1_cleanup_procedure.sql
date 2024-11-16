DROP PROCEDURE IF EXISTS cleanup_old_logs;

DELIMITER //

CREATE PROCEDURE cleanup_old_logs(IN retention_days INT)
BEGIN
    DECLARE cleanup_date TIMESTAMP;
    SET cleanup_date = DATE_SUB(NOW(), INTERVAL retention_days DAY);
    
    DELETE FROM system_logs
    WHERE created_at < cleanup_date
    AND level NOT IN ('error', 'critical');
    
    DELETE FROM audit_logs
    WHERE created_at < cleanup_date;
    
    DELETE FROM status_change_logs
    WHERE created_at < cleanup_date;
END //

DELIMITER ;

INSERT INTO migrations (version, name) VALUES ('4.1', 'V4_1__cleanup_procedure.sql');