# Tarea: Configuración de Linting y Pre-commit Hooks

## Objetivos Cumplidos

### 1. Configuración de SonarLint y Linters
✅ **SonarLint**:
- Instalado en VS Code como extensión
- Configurado para analizar código PHP y JavaScript
- Detecta problemas de calidad de código en tiempo real

✅ **ESLint**:
- Configurado en `.eslintrc.json`
- Reglas personalizadas para el proyecto
- Soporte para TypeScript

✅ **Prettier**:
- Configurado en `.prettierrc`
- Integrado con ESLint
- Formateo automático del código

### 2. Configuración de .editorconfig
✅ **Archivo .editorconfig**:
```ini
[*]
charset = utf-8
end_of_line = lf
indent_size = 4
indent_style = tab
insert_final_newline = true
trim_trailing_whitespace = true
```

### 3. Regla para forzar tabs
✅ **Configuración en .prettierrc**:
```json
{
  "useTabs": true,
  "tabWidth": 4,
  "singleQuote": true,
  "trailingComma": "es5",
  "semi": true,
  "printWidth": 100
}
```

### 4. Pre-commit con Husky
✅ **Configuración del hook**:
```sh
#!/bin/sh
. "$(dirname "$0")/_/husky.sh"

npx lint-staged
```

✅ **Configuración de lint-staged**:
```json
"lint-staged": {
  "*.php": [
    "prettier --write"
  ],
  "*.{js,jsx,ts,tsx}": [
    "prettier --write",
    "eslint --fix"
  ]
}
```

## Pasos Realizados

1. **Instalación de dependencias**:
   ```bash
   npm install --save-dev husky lint-staged prettier eslint @typescript-eslint/parser @typescript-eslint/eslint-plugin @prettier/plugin-php
   ```

2. **Configuración de Husky**:
   ```bash
   npx husky init
   ```

3. **Configuración de archivos**:
   - `.editorconfig` para reglas básicas de formato
   - `.prettierrc` para configuración de Prettier
   - `.eslintrc.json` para reglas de ESLint
   - `.husky/pre-commit` para el hook de pre-commit

4. **Verificación de funcionamiento**:
   - Modificación de archivos con espacios
   - Commit para verificar la conversión a tabs
   - Verificación de reglas de linting

## Demostración Práctica

Para verificar que todo funciona:

1. Modificar un archivo PHP o JavaScript usando espacios
2. Hacer commit del archivo
3. El sistema automáticamente:
   - Convertirá los espacios a tabs
   - Aplicará las reglas de formato
   - Verificará el código con los linters

## Resultados

- ✅ Código formateado consistentemente
- ✅ Uso de tabs en lugar de espacios
- ✅ Verificación automática antes de cada commit
- ✅ Integración con el editor (VS Code)
- ✅ Detección temprana de problemas de código 