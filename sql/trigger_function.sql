CREATE  OR REPLACE FUNCTION log_users() RETURNS trigger AS $log_users$
BEGIN

  INSERT INTO log.users SELECT * FROM public.users WHERE id = NEW.id;

  RETURN NEW;
END;
$log_users$ LANGUAGE plpgsql;

CREATE TRIGGER log_insert_users AFTER INSERT ON users
  FOR EACH ROW EXECUTE PROCEDURE log_users();

CREATE TRIGGER log_update_users AFTER UPDATE ON users
  FOR EACH ROW EXECUTE PROCEDURE log_users();