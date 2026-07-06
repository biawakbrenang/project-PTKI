import mysql from 'mysql2/promise';

let pool;

/**
 * Returns a shared MySQL connection pool.
 * Works with any MySQL-compatible host (TiDB Serverless, Aiven for MySQL,
 * Railway, etc.) — just set the DB_* environment variables.
 */
export function getPool() {
  if (!pool) {
    pool = mysql.createPool({
      host: process.env.DB_HOST,
      port: Number(process.env.DB_PORT || 3306),
      user: process.env.DB_USER,
      password: process.env.DB_PASSWORD,
      database: process.env.DB_NAME,
      waitForConnections: true,
      connectionLimit: 5,
      maxIdle: 5,
      idleTimeout: 60000,
      queueLimit: 0,
      ssl: process.env.DB_SSL === 'true' ? { minVersion: 'TLSv1.2' } : undefined,
    });
  }
  return pool;
}

/** Run a query and return rows. */
export async function query(sql, params = []) {
  const [rows] = await getPool().execute(sql, params);
  return rows;
}
