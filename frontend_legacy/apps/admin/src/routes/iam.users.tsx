import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { useUsers, UserService, User } from '@sodars/module-iam';
import { useState, useEffect } from 'react';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/iam/users',
  component: UsersComponent,
});

function UsersComponent() {
  const { data: initialUsers, isLoading, error } = useUsers();
  const [users, setUsers] = useState<User[]>([]);

  useEffect(() => {
    if (initialUsers) {
      setUsers(initialUsers);
    }
  }, [initialUsers]);

  const handleLockUser = async (user: User) => {
    try {
      const updated = await UserService.lockUserAccount(user);
      // Update state locally
      setUsers(prev => prev.map(u => u.id === user.id ? updated : u));
    } catch (err) {
      console.error('[UsersComponent] Failed to lock user:', err);
    }
  };

  return (
    <div className="space-y-6 font-sans">
      <div className="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center">
            <SodarsIcon name="settings" className="text-indigo-600 mr-2.5" size={24} />
            Users Directory
          </h2>
          <p className="text-slate-500 text-sm">Manage user authentication profiles and permissions allocations.</p>
        </div>
      </div>

      <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        {isLoading ? (
          <div className="text-center py-12 text-slate-400 text-xs">
            Loading user list profiles...
          </div>
        ) : error ? (
          <div className="text-center py-12 text-red-500 text-xs">
            Error loading users list: {error.message}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse text-xs">
              <thead>
                <tr className="border-b border-slate-200 text-slate-500 font-semibold">
                  <th className="py-3">User ID</th>
                  <th className="py-3">Name</th>
                  <th className="py-3">Email Address</th>
                  <th className="py-3">Role Code</th>
                  <th className="py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {users.map(u => (
                  <tr key={u.id} className="text-slate-700 hover:bg-slate-50 transition-colors">
                    <td className="py-3 font-semibold text-slate-900">{u.id}</td>
                    <td className="py-3">{u.name}</td>
                    <td className="py-3 font-mono text-[11px]">{u.email}</td>
                    <td className="py-3">
                      <span className="px-2 py-0.5 bg-slate-100 rounded text-slate-650 uppercase font-bold text-[9px]">
                        {u.roleId}
                      </span>
                    </td>
                    <td className="py-3 text-right">
                      {u.isLocked ? (
                        <span className="px-2.5 py-1 text-[10px] font-bold bg-red-50 text-red-700 rounded-full">
                          LOCKED
                        </span>
                      ) : (
                        <button
                          onClick={() => handleLockUser(u)}
                          className="px-3 py-1 bg-slate-900 text-white rounded text-[10px] font-bold hover:bg-slate-800 transition-all cursor-pointer"
                        >
                          Lock Account
                        </button>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
export default UsersComponent;
