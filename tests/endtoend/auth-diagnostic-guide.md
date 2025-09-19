# 🔍 Guide de Diagnostic d'Authentification

## ⚠️ **PROBLÈME IDENTIFIÉ :**

Les tests montrent que **les sessions ne persistent pas** et les utilisateurs sont redirigés vers la page de connexion :

```
→ 302: http://localhost:8888/it-expect/login
```

## 🔧 **SOLUTIONS À TESTER :**

### **1. Vérifier l'Utilisateur dans la Base de Données**

Connectez-vous à votre base de données et vérifiez si l'utilisateur existe :

```sql
SELECT * FROM users WHERE user_email = 'admin@cinetech.com';
```

**Si l'utilisateur n'existe pas :**
- Créez-le manuellement dans votre base de données
- Ou utilisez un utilisateur existant dans les tests

### **2. Vérifier la Configuration des Sessions PHP**

Dans votre fichier `config/config.php` ou équivalent :

```php
// Vérifiez ces paramètres
session_start();
ini_set('session.cookie_lifetime', 3600); // 1 heure
ini_set('session.gc_maxlifetime', 3600);
```

### **3. Tester Manuellement l'Authentification**

1. Ouvrez votre navigateur
2. Allez sur `http://localhost:8888/it-expect/?r=login`
3. Connectez-vous avec `admin@cinetech.com` / `admin123`
4. Vérifiez si vous êtes redirigé correctement
5. Essayez d'accéder à `http://localhost:8888/it-expect/?r=profile`

### **4. Créer un Utilisateur de Test**

Si aucun utilisateur n'existe, créez-en un :

```sql
INSERT INTO users (user_firstname, user_lastname, user_email, user_password, created_at) 
VALUES ('Admin', 'Test', 'admin@cinetech.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());
```

**Mot de passe :** `password` (haché avec bcrypt)

### **5. Utiliser un Utilisateur Existant**

Si vous avez déjà des utilisateurs dans votre DB, modifiez le fichier de test :

```javascript
const realUser = {
  email: 'votre.email.existant@example.com', // Email réel de votre DB
  password: 'votre_mot_de_passe_reel' // Mot de passe réel
};
```

---

## 🚀 **FICHIER DE TEST CORRIGÉ :**

J'ai créé `cypress-fixed-auth-tests.cy.js` qui :

✅ **S'adapte aux redirections**
✅ **Fournit des diagnostics détaillés**
✅ **Teste les fonctionnalités conditionnellement**
✅ **Donne des conseils de résolution**

---

## 📊 **COMMANDES POUR DIAGNOSTIQUER :**

### **1. Lancer le diagnostic complet :**
```bash
npx cypress open
# Sélectionner : cypress-fixed-auth-tests.cy.js
```

### **2. Voir les logs détaillés :**
Dans l'interface Cypress, regardez la console pour :
- `📍 URL après connexion`
- `✅/❌ État des pages protégées`
- `🍪 Cookies actifs`
- `💡 Conseils de résolution`

---

## 🎯 **RÉSULTATS ATTENDUS :**

### **Si l'utilisateur existe et fonctionne :**
- `✅ Connexion réussie - redirigé vers home`
- `✅ Page profile: Accessible`
- `✅ Page favoris accessible`

### **Si l'utilisateur n'existe pas :**
- `❌ Connexion échouée - reste sur login`
- `❌ Page profile: Redirection vers login`
- `💡 Vérifiez les identifiants dans realUser`

---

## 🔧 **ACTIONS À PRENDRE :**

1. **Lancez le diagnostic** : `cypress-fixed-auth-tests.cy.js`
2. **Identifiez le problème** : Consultez les logs
3. **Corrigez selon le diagnostic** :
   - Créer l'utilisateur manquant
   - Corriger les identifiants
   - Vérifier la config des sessions
4. **Relancez les tests originaux**

Une fois l'authentification corrigée, tous les 28 tests devraient passer ! 🎉
