-- PostgreSQL schema. Existing tables/data are retained; no DROP statements.
CREATE TABLE IF NOT EXISTS blog_posts (
 id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
 slug text NOT NULL UNIQUE, title text NOT NULL, client_name text NOT NULL,
 category text NOT NULL, excerpt text NOT NULL, body text NOT NULL,
 services text[] NOT NULL DEFAULT '{}', accent text NOT NULL DEFAULT 'green',
 status text NOT NULL DEFAULT 'published', featured boolean NOT NULL DEFAULT false,
 published_at timestamptz, created_at timestamptz NOT NULL DEFAULT now(),
 updated_at timestamptz NOT NULL DEFAULT now()
);
CREATE TABLE IF NOT EXISTS usabime_requests (
 id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
 reference text NOT NULL UNIQUE, full_name text NOT NULL, phone_number text NOT NULL,
 email text, business_name text NOT NULL, business_type text NOT NULL,
 service_type text NOT NULL, business_description text NOT NULL,
 industry text NOT NULL, location text NOT NULL, public_contact text NOT NULL DEFAULT '',
 brand_colors text NOT NULL DEFAULT '', asset_links text[] NOT NULL DEFAULT '{}',
 website_references text[] NOT NULL DEFAULT '{}', preferred_timeline text NOT NULL,
 target_date date, additional_details text NOT NULL DEFAULT '',
 privacy_accepted boolean NOT NULL DEFAULT true, privacy_accepted_at timestamptz NOT NULL DEFAULT now(),
 status text NOT NULL DEFAULT 'new', admin_notes text NOT NULL DEFAULT '',
 created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now()
);
CREATE TABLE IF NOT EXISTS usabime_proposals (
 id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
 request_id integer NOT NULL UNIQUE REFERENCES usabime_requests(id) ON DELETE CASCADE,
 title text NOT NULL, introduction text NOT NULL DEFAULT '', scope text NOT NULL DEFAULT '',
 currency text NOT NULL DEFAULT 'NGN', line_items jsonb NOT NULL DEFAULT '[]',
 payment_terms text NOT NULL DEFAULT '', timeline text NOT NULL DEFAULT '',
 valid_until text, terms text NOT NULL DEFAULT '', status text NOT NULL DEFAULT 'draft',
 created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now()
);
CREATE TABLE IF NOT EXISTS contact_inquiries (
 id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
 reference text NOT NULL UNIQUE, full_name text NOT NULL, email text NOT NULL,
 phone_number text NOT NULL DEFAULT '', service text NOT NULL, budget text NOT NULL DEFAULT '',
 message text NOT NULL, privacy_accepted boolean NOT NULL DEFAULT true,
 privacy_accepted_at timestamptz NOT NULL DEFAULT now(),
 status text NOT NULL DEFAULT 'new', admin_notes text NOT NULL DEFAULT '',
 created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now()
);
CREATE TABLE IF NOT EXISTS security_limits (
 key text PRIMARY KEY, window_start bigint NOT NULL, attempts integer NOT NULL
);