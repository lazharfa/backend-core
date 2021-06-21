create function fill_donation_column_func() returns trigger
    language plpgsql
as
$$
DECLARE
    var_donor_id  integer;
    var_donor_phone varchar(50);

BEGIN

    var_donor_phone = new.donor_phone;
    var_donor_phone = regexp_replace(var_donor_phone, '^[+]', '');
    var_donor_phone = regexp_replace(var_donor_phone, '^[0]', '62');

    new.donor_phone := var_donor_phone;

    if new.donor_id is null then

        var_donor_id := (SELECT id FROM users WHERE member_id = new.member_id AND (email = new.donor_email OR phone_number = var_donor_phone) LIMIT 1);

        if var_donor_id is null and (new.donor_email is not null or new.donor_phone is not null) then

            INSERT INTO users (member_id, full_name, email, phone_number) VALUES (new.member_id, new.donor_name, new.donor_email, var_donor_phone);

        end if;

        new.donor_id := (SELECT id FROM users WHERE member_id = new.member_id AND (email = new.donor_email OR phone_number = var_donor_phone) LIMIT 1);

    end if;

    return new;
END;
$$;

drop trigger set_donor_id_tgr on donations;
CREATE TRIGGER set_donor_id_tgr
  BEFORE INSERT
  ON donations
  FOR EACH ROW
EXECUTE PROCEDURE fill_donation_column_func();





create or replace function public.func_change_priority_campaigns() returns trigger
    language plpgsql
as $$
BEGIN

    new.priority = 3;

    if (now() + interval '7 hours')::date < new.expired_at then

        if new.priority != 1 then
            new.priority = 2;
        end if;

    ELSE

        new.priority = 3;

    end if;

    return new;
END;
$$;

CREATE TRIGGER trigger_change_priority_campaigns
    BEFORE UPDATE
    OF expired_at
    ON campaigns
--     FOR EACH ROW
--     WHEN (old.expired_at IS DISTINCT FROM new.expired_at)
EXECUTE PROCEDURE func_change_priority_campaigns();


-- ScheduleChangePriorityCampaign
