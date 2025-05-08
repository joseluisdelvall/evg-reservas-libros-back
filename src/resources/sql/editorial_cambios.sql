-- Modificaciones a la tabla EDITORIAL para añadir múltiples teléfonos y correos

-- 1. Eliminar las restricciones de unicidad existentes para permitir valores duplicados
ALTER TABLE EDITORIAL DROP CONSTRAINT IF EXISTS editorial_telefono_uq;
ALTER TABLE EDITORIAL DROP CONSTRAINT IF EXISTS editorial_correo_uq;

-- 2. Renombrar las columnas existentes para mantener consistencia y asegurar que aceptan NULL
ALTER TABLE EDITORIAL CHANGE COLUMN telefono telefono1 VARCHAR(20) NULL DEFAULT NULL;
ALTER TABLE EDITORIAL CHANGE COLUMN correo correo1 VARCHAR(100) NULL DEFAULT NULL;

-- 3. Añadir nuevos campos para teléfonos y correos adicionales con NULL explícito
ALTER TABLE EDITORIAL ADD COLUMN telefono2 VARCHAR(20) NULL DEFAULT NULL;
ALTER TABLE EDITORIAL ADD COLUMN telefono3 VARCHAR(20) NULL DEFAULT NULL;
ALTER TABLE EDITORIAL ADD COLUMN correo2 VARCHAR(100) NULL DEFAULT NULL;
ALTER TABLE EDITORIAL ADD COLUMN correo3 VARCHAR(100) NULL DEFAULT NULL;

-- 4. Añadir campo de activo si no existe
ALTER TABLE EDITORIAL ADD COLUMN IF NOT EXISTS activo BOOLEAN NOT NULL DEFAULT TRUE;

-- 5. Actualizar los registros existentes para convertir cadenas vacías a NULL
UPDATE EDITORIAL SET 
  telefono1 = NULLIF(telefono1, ''),
  telefono2 = NULLIF(telefono2, ''),
  telefono3 = NULLIF(telefono3, ''),
  correo1 = NULLIF(correo1, ''),
  correo2 = NULLIF(correo2, ''),
  correo3 = NULLIF(correo3, ''); 