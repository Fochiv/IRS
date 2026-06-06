{pkgs}: {
  deps = [
    pkgs.php82Extensions.pdo_mysql
    pkgs.php82Extensions.mysqli
    pkgs.mariadb
  ];
}
