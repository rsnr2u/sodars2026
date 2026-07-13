import React, { useState } from 'react';
import { Route as TSRoute } from '@tanstack/react-router';
import { Route as rootRoute } from './__root';
import { SessionManager } from '@sodars/auth';
import { Button } from '@sodars/design-system';

export const Route = new TSRoute({
  getParentRoute: () => rootRoute,
  path: '/login',
  component: LoginComponent,
});

function LoginComponent() {
  const [email, setEmail] = useState('admin@sodars.com');
  const [password, setPassword] = useState('password');
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (email === 'admin@sodars.com' && password === 'password') {
      // Mock login response DTO
      SessionManager.login(
        'mock-access-token-jwt',
        'mock-refresh-token-jwt',
        {
          id: 'usr-999-id',
          name: 'Administrator',
          email: 'admin@sodars.com',
          roles: ['super_admin'],
          permissions: ['*'],
          organizations: [
            {
              id: 'org-999-id',
              name: 'Operations Corp',
              slug: 'ops',
            }
          ]
        }
      );
      window.location.href = '/';
    } else {
      setError('Invalid credentials. Use admin@sodars.com / password.');
    }
  };

  return (
    <div className="min-h-screen bg-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
      <div className="sm:mx-auto w-full sm:max-w-md text-center">
        <h2 className="text-3xl font-extrabold text-white tracking-tight">SODARS Platform</h2>
        <p className="mt-2 text-sm text-slate-400">Sign in to your administration panel</p>
      </div>

      <div className="mt-8 sm:mx-auto w-full sm:max-w-md">
        <div className="bg-slate-900 border border-slate-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
          <form className="space-y-6" onSubmit={handleSubmit}>
            {error && (
              <div className="p-3 bg-red-950/50 border border-red-900 rounded text-red-400 text-xs font-semibold">
                {error}
              </div>
            )}
            <div>
              <label className="block text-xs font-bold text-slate-300 uppercase tracking-wider">Email Address</label>
              <input
                type="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="mt-2 block w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-md text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-300 uppercase tracking-wider">Password</label>
              <input
                type="password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="mt-2 block w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-md text-slate-200 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div>
              <Button type="submit" variant="primary" className="w-full justify-center">
                Sign In
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
