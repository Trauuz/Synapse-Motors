create extension if not exists pgcrypto;

create table if not exists public.users (
    id uuid primary key default gen_random_uuid(),
    auth_user_id uuid not null unique,
    name text not null,
    email text not null unique,
    role text not null default 'Buyer' check (role in ('Buyer', 'Admin')),
    address text not null,
    contact_no text not null,
    created_at timestamptz not null default timezone('utc', now()),
    updated_at timestamptz not null default timezone('utc', now())
);

create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
    new.updated_at = timezone('utc', now());
    return new;
end;
$$;

drop trigger if exists set_users_updated_at on public.users;

create trigger set_users_updated_at
before update on public.users
for each row
execute function public.set_updated_at();

alter table public.users enable row level security;

drop policy if exists "service role can manage users" on public.users;
create policy "service role can manage users"
on public.users
for all
to service_role
using (true)
with check (true);

drop policy if exists "authenticated users can read own profile" on public.users;
create policy "authenticated users can read own profile"
on public.users
for select
to authenticated
using (auth.uid() = auth_user_id);
