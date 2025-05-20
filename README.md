# Déchiffrer un testament crypté

## Prérequis

- PHP avec l’extension OpenSSL activée
- Le fichier `testament.txt` généré par l’application

## Étapes pour déchiffrer le message

1. **Ouvrez l’application web sur n’importe quel ordinateur avec ce code source.**
2. **Assurez-vous que le fichier `testament.txt` est présent dans le dossier de l’application.**
3. **Dans la section “Déchiffrer un testament depuis le fichier” :**
   - Répondez correctement aux trois questions affichées (les réponses doivent être exactement celles utilisées lors du chiffrement, minuscules et courte).
   - Cliquez sur le bouton “Déchiffrer”.

4. **Le message du testament apparaîtra dans la zone “Message déchiffré”.**

## Sécurité

- Les réponses aux questions sont stockées chiffrées dans `testament.txt`.
- La clé de déchiffrement du message est la concaténation exacte des trois réponses.
- Le secret utilisé pour chiffrer les réponses (`questions-secret`) doit rester dans le code et ne pas être partagé.

## Utilisation sur un autre ordinateur

- Copiez le dossier de l’application et le fichier `testament.txt` sur l’autre ordinateur.
- Suivez les mêmes étapes ci-dessus pour déchiffrer le message.

