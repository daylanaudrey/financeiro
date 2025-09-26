<?php
class IntegrationConfig {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Busca todas as configurações de uma integração
     */
    public function getConfigsByType($organizationId, $integrationType) {
        $sql = "SELECT config_key, config_value, is_encrypted
                FROM integration_configs
                WHERE organization_id = ? AND integration_type = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$organizationId, $integrationType]);

        $configs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $value = $row['config_value'];

            // Se for criptografado, descriptografar (implementar criptografia depois)
            if ($row['is_encrypted']) {
                // TODO: Implementar descriptografia
            }

            $configs[$row['config_key']] = $value;
        }

        return $configs;
    }

    /**
     * Busca uma configuração específica
     */
    public function getConfig($organizationId, $integrationType, $configKey) {
        $sql = "SELECT config_value, is_encrypted
                FROM integration_configs
                WHERE organization_id = ? AND integration_type = ? AND config_key = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$organizationId, $integrationType, $configKey]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $value = $row['config_value'];

        // Se for criptografado, descriptografar
        if ($row['is_encrypted']) {
            // TODO: Implementar descriptografia
        }

        return $value;
    }

    /**
     * Salva ou atualiza uma configuração
     */
    public function saveConfig($organizationId, $integrationType, $configKey, $configValue, $isEncrypted = false) {
        $value = $configValue;

        // Se for para criptografar, criptografar o valor
        if ($isEncrypted) {
            // TODO: Implementar criptografia
        }

        $sql = "INSERT INTO integration_configs (organization_id, integration_type, config_key, config_value, is_encrypted)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                config_value = VALUES(config_value),
                is_encrypted = VALUES(is_encrypted),
                updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$organizationId, $integrationType, $configKey, $value, $isEncrypted ? 1 : 0]);
    }

    /**
     * Salva múltiplas configurações de uma vez
     */
    public function saveConfigs($organizationId, $integrationType, $configs, $encryptedKeys = []) {
        $this->pdo->beginTransaction();

        try {
            foreach ($configs as $key => $value) {
                $isEncrypted = in_array($key, $encryptedKeys);
                $this->saveConfig($organizationId, $integrationType, $key, $value, $isEncrypted);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Remove uma configuração
     */
    public function deleteConfig($organizationId, $integrationType, $configKey) {
        $sql = "DELETE FROM integration_configs
                WHERE organization_id = ? AND integration_type = ? AND config_key = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$organizationId, $integrationType, $configKey]);
    }

    /**
     * Remove todas as configurações de uma integração
     */
    public function deleteConfigsByType($organizationId, $integrationType) {
        $sql = "DELETE FROM integration_configs
                WHERE organization_id = ? AND integration_type = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$organizationId, $integrationType]);
    }
}
?>