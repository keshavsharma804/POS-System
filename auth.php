<?php
require_once 'config.php';

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Check if user has a specific permission
     */
    public function hasPermission($userId, $permission) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                JOIN users u ON u.role_id = rp.role_id
                WHERE u.id = ? AND p.name = ?
            ");
            $stmt->execute([$userId, $permission]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Permission check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all permissions for a user
     */
    public function getUserPermissions($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.name FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                JOIN users u ON u.role_id = rp.role_id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Get permissions failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM users 
                WHERE id = ? AND role_id = ?
            ");
            $stmt->execute([$userId, ADMIN_ROLE]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Admin check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all roles
     */
    public function getAllRoles() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM roles ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get roles failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all permissions
     */
    public function getAllPermissions() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM permissions ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get permissions failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get permissions for a role
     */
    public function getRolePermissions($roleId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.id, p.name FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?
            ");
            $stmt->execute([$roleId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get role permissions failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update role permissions
     */
    public function updateRolePermissions($roleId, $permissionIds) {
        try {
            $this->pdo->beginTransaction();
            
            // Delete existing permissions
            $stmt = $this->pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->execute([$roleId]);
            
            // Insert new permissions
            $stmt = $this->pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($permissionIds as $permId) {
                $stmt->execute([$roleId, $permId]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Update role permissions failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new role
     */
    public function createRole($name, $description) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Create role failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user role
     */
    public function updateUserRole($userId, $roleId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
            return $stmt->execute([$roleId, $userId]);
        } catch (PDOException $e) {
            error_log("Update user role failed: " . $e->getMessage());
            return false;
        }
    }


    // Add these methods to your Auth class in auth.php

/**
 * Check if current user has permission
 */
public function checkPermission($permission) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    return $this->hasPermission($_SESSION['user_id'], $permission);
}

/**
 * Get all users with their roles
 */
public function getAllUsersWithRoles() {
    try {
        $stmt = $this->pdo->query("
            SELECT u.id, u.username, u.email, r.name as role_name, r.id as role_id 
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.username
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get users with roles failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Delete a role (only if no users are assigned to it)
 */
        public function deleteRole($roleId) {
            try {
                // Check if any users have this role
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE role_id = ?");
                $stmt->execute([$roleId]);
                $userCount = $stmt->fetchColumn();
                
                if ($userCount > 0) {
                    return ['success' => false, 'message' => 'Cannot delete role assigned to users'];
                }
                
                $this->pdo->beginTransaction();
                
                // Delete role permissions first
                $stmt = $this->pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
                $stmt->execute([$roleId]);
                
                // Then delete the role
                $stmt = $this->pdo->prepare("DELETE FROM roles WHERE id = ?");
                $stmt->execute([$roleId]);
                
                $this->pdo->commit();
                return ['success' => true, 'message' => 'Role deleted successfully'];
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                error_log("Delete role failed: " . $e->getMessage());
                return ['success' => false, 'message' => 'Failed to delete role'];
            }
        }

/**
 * Update role information
 */
    public function updateRole($roleId, $name, $description) {
        try {
            $stmt = $this->pdo->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $roleId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Update role failed: " . $e->getMessage());
            return false;
        }
    }
}
?>