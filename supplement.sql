alter table shows drop index idx_location2;
alter table venue drop index idx_location2;
alter table wiki_links drop primary key;
alter table wiki_links add id int not null primary key auto_increment;
alter table wiki_remote_pages drop primary key;
alter table wiki_remote_pages add id int not null primary key auto_increment;
alter table service_keys modify service_key varchar(255) primary key;